<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Building
 *
 * @property int $id
 * @property int|null $user_id
 * @property string $name
 * @property string|null $address
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * 
 * Relationships:
 * @property-read \App\Models\User|null $user
 * @property-read \Illuminate\Database\Eloquent\Collection|Task[] $tasks
 */
class Building extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'address',
    ];

    /**
     * Get the user that owns the building.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all tasks associated with the building.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}
