<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\TaskComment
 *
 * @property int $id
 * @property int $task_id
 * @property int|null $created_by
 * @property string $body
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * Relationships:
 * @property-read \App\Models\Task $task
 * @property-read \App\Models\User|null $creator
 */
class TaskComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'created_by',
        'body',
    ];

    /**
     * The task this comment belongs to.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * The user who created the comment.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withDefault();
    }
}
