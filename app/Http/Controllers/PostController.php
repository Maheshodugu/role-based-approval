<?php

namespace App\Http\Controllers;

use App\Http\Requests\RejectPostRequest;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Post;
use App\Models\PostLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Post::class);

        $user = $request->user();

        $posts = Post::query()
            ->select(['id', 'user_id', 'title', 'body', 'status', 'approved_by', 'rejected_reason', 'created_at', 'updated_at'])
            ->with(['author:id,name,email', 'approver:id,name,email'])
            ->when($user->hasRole('Author'), fn ($query) => $query->where('user_id', $user->id))
            ->latest()
            ->paginate(15);

        return response()->json($posts);
    }

    public function store(StorePostRequest $request)
    {
        $user = $request->user();

        $post = DB::transaction(function () use ($request, $user) {
            $post = Post::create([
                'user_id' => $user->id,
                'title' => $request->validated('title'),
                'body' => $request->validated('body'),
                'status' => Post::STATUS_PENDING,
            ]);

            PostLog::create([
                'post_id' => $post->id,
                'action' => PostLog::ACTION_CREATED,
                'performed_by' => $user->id,
            ]);

            return $post;
        });

        return response()->json($post->load(['author:id,name,email', 'approver:id,name,email']), 201);
    }

    public function update(UpdatePostRequest $request, int $id)
    {
        $post = Post::query()->findOrFail($id);
        $this->authorize('update', $post);

        $post->update($request->validated());

        return response()->json($post->fresh(['author:id,name,email', 'approver:id,name,email']));
    }

    public function approve(Request $request, int $id)
    {
        $post = Post::query()->findOrFail($id);
        $this->authorize('approve', $post);

        $user = $request->user();

        $post = DB::transaction(function () use ($post, $user) {
            $post->update([
                'status' => Post::STATUS_APPROVED,
                'approved_by' => $user->id,
                'rejected_reason' => null,
            ]);

            PostLog::create([
                'post_id' => $post->id,
                'action' => PostLog::ACTION_APPROVED,
                'performed_by' => $user->id,
            ]);

            return $post;
        });

        return response()->json($post->fresh(['author:id,name,email', 'approver:id,name,email']));
    }

    public function reject(RejectPostRequest $request, int $id)
    {
        $post = Post::query()->findOrFail($id);
        $this->authorize('reject', $post);

        $user = $request->user();

        $post = DB::transaction(function () use ($post, $request, $user) {
            $post->update([
                'status' => Post::STATUS_REJECTED,
                'approved_by' => null,
                'rejected_reason' => $request->validated('rejected_reason'),
            ]);

            PostLog::create([
                'post_id' => $post->id,
                'action' => PostLog::ACTION_REJECTED,
                'performed_by' => $user->id,
            ]);

            return $post;
        });

        return response()->json($post->fresh(['author:id,name,email', 'approver:id,name,email']));
    }

    public function destroy(Request $request, int $id)
    {
        $post = Post::query()->findOrFail($id);
        $this->authorize('delete', $post);

        $user = $request->user();

        DB::transaction(function () use ($post, $user) {
            PostLog::create([
                'post_id' => $post->id,
                'action' => PostLog::ACTION_DELETED,
                'performed_by' => $user->id,
            ]);

            $post->delete();
        });

        return response()->json([], 204);
    }
}
