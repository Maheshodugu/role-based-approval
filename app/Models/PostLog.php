<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostLog extends Model
{
    use HasFactory;

    public const ACTION_CREATED = 'created';
    public const ACTION_APPROVED = 'approved';
    public const ACTION_REJECTED = 'rejected';
    public const ACTION_DELETED = 'deleted';

    protected $fillable = [
        'post_id',
        'action',
        'performed_by',
    ];

    protected function casts(): array
    {
        return [
            'post_id' => 'integer',
            'performed_by' => 'integer',
        ];
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
