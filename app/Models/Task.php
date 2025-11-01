<?php

namespace App\Models;

use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Task
 *
 * @property int $id
 * @property int $building_id
 * @property int|null $created_by
 * @property int|null $assigned_to
 * @property string $title
 * @property string|null $description
 * @property TaskStatus $status
 * @property \Illuminate\Support\Carbon|null $due_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * Relationships:
 * @property-read \App\Models\Building $building
 * @property-read \App\Models\User|null $creator
 * @property-read \App\Models\User|null $assignee
 * @property-read \Illuminate\Database\Eloquent\Collection|TaskComment[] $comments
 */
class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'building_id',
        'created_by',
        'assigned_to',
        'title',
        'description',
        'status',
        'due_at',
    ];

    protected $casts = [
        'status' => TaskStatus::class,
        'due_at' => 'datetime',
    ];

    /**
     * The building this task belongs to.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    /**
     * The user who created the task.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * The user assigned to work on this task.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get comments related to the task.
     *
     * @return  \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class);
    }
}
