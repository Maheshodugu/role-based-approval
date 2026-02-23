<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\PostLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PostApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['Author', 'Manager', 'Admin'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }
    }

    public function test_author_can_create_post_and_created_log_is_recorded(): void
    {
        $author = $this->createUserWithRole('Author');
        Sanctum::actingAs($author);

        $response = $this->postJson('/api/posts', [
            'title' => 'My first post',
            'body' => 'Content body',
        ]);

        $response->assertCreated()
            ->assertJsonPath('title', 'My first post')
            ->assertJsonPath('status', Post::STATUS_PENDING)
            ->assertJsonPath('user_id', $author->id);

        $postId = $response->json('id');

        $this->assertDatabaseHas('post_logs', [
            'post_id' => $postId,
            'action' => PostLog::ACTION_CREATED,
            'performed_by' => $author->id,
        ]);
    }

    public function test_author_can_update_own_post_but_cannot_update_others(): void
    {
        $author = $this->createUserWithRole('Author');
        $otherAuthor = $this->createUserWithRole('Author');

        $ownPost = Post::factory()->create(['user_id' => $author->id]);
        $otherPost = Post::factory()->create(['user_id' => $otherAuthor->id]);

        Sanctum::actingAs($author);

        $this->putJson("/api/posts/{$ownPost->id}", [
            'title' => 'Updated title',
            'body' => 'Updated body',
        ])->assertOk()->assertJsonPath('title', 'Updated title');

        $this->putJson("/api/posts/{$otherPost->id}", [
            'title' => 'Hack title',
            'body' => 'Hack body',
        ])->assertForbidden();
    }

    public function test_author_only_sees_own_posts_in_index(): void
    {
        $author = $this->createUserWithRole('Author');
        $otherAuthor = $this->createUserWithRole('Author');

        Post::factory()->create(['user_id' => $author->id, 'title' => 'Mine']);
        Post::factory()->create(['user_id' => $otherAuthor->id, 'title' => 'Not mine']);

        Sanctum::actingAs($author);

        $response = $this->getJson('/api/posts');
        $response->assertOk();

        $titles = collect($response->json('data'))->pluck('title');

        $this->assertTrue($titles->contains('Mine'));
        $this->assertFalse($titles->contains('Not mine'));
    }

    public function test_manager_can_view_all_posts_and_approve_with_log(): void
    {
        $manager = $this->createUserWithRole('Manager');
        $authorA = $this->createUserWithRole('Author');
        $authorB = $this->createUserWithRole('Author');

        $post = Post::factory()->create(['user_id' => $authorA->id]);
        Post::factory()->create(['user_id' => $authorB->id]);

        Sanctum::actingAs($manager);

        $indexResponse = $this->getJson('/api/posts');
        $indexResponse->assertOk();
        $this->assertCount(2, $indexResponse->json('data'));

        $this->postJson("/api/posts/{$post->id}/approve")
            ->assertOk()
            ->assertJsonPath('status', Post::STATUS_APPROVED)
            ->assertJsonPath('approved_by', $manager->id);

        $this->assertDatabaseHas('post_logs', [
            'post_id' => $post->id,
            'action' => PostLog::ACTION_APPROVED,
            'performed_by' => $manager->id,
        ]);
    }

    public function test_manager_can_reject_with_reason_and_log(): void
    {
        $manager = $this->createUserWithRole('Manager');
        $author = $this->createUserWithRole('Author');
        $post = Post::factory()->create(['user_id' => $author->id]);

        Sanctum::actingAs($manager);

        $this->postJson("/api/posts/{$post->id}/reject", [
            'rejected_reason' => 'Does not meet quality requirements',
        ])->assertOk()
            ->assertJsonPath('status', Post::STATUS_REJECTED)
            ->assertJsonPath('rejected_reason', 'Does not meet quality requirements')
            ->assertJsonPath('approved_by', null);

        $this->assertDatabaseHas('post_logs', [
            'post_id' => $post->id,
            'action' => PostLog::ACTION_REJECTED,
            'performed_by' => $manager->id,
        ]);
    }

    public function test_author_cannot_approve_or_reject_posts(): void
    {
        $author = $this->createUserWithRole('Author');
        $owner = $this->createUserWithRole('Author');
        $post = Post::factory()->create(['user_id' => $owner->id]);

        Sanctum::actingAs($author);

        $this->postJson("/api/posts/{$post->id}/approve")
            ->assertForbidden();

        $this->postJson("/api/posts/{$post->id}/reject", [
            'rejected_reason' => 'No',
        ])->assertForbidden();
    }

    public function test_only_admin_can_delete_post_and_deletion_is_logged(): void
    {
        $admin = $this->createUserWithRole('Admin');
        $manager = $this->createUserWithRole('Manager');
        $author = $this->createUserWithRole('Author');

        $post = Post::factory()->create(['user_id' => $author->id]);

        Sanctum::actingAs($manager);
        $this->deleteJson("/api/posts/{$post->id}")
            ->assertForbidden();

        Sanctum::actingAs($admin);
        $this->deleteJson("/api/posts/{$post->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);

        $this->assertDatabaseHas('post_logs', [
            'post_id' => $post->id,
            'action' => PostLog::ACTION_DELETED,
            'performed_by' => $admin->id,
        ]);
    }

    private function createUserWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
