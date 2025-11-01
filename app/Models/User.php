<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Buildings created by the user.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function buildings(): HasMany
    {
        return $this->hasMany(Building::class);
    }

    /**
     * Tasks created by the user.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function createdTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'created_by');
    }

    /**
     * Tasks assigned to the user.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assignedTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    /**
     * Comments created by the user.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function taskComments(): HasMany
    {
        return $this->hasMany(TaskComment::class, 'created_by');
    }
}
