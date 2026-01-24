<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'timezone',
        'is_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
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
        'password' => 'hashed',
        'is_admin' => 'boolean',
    ];

    /**
     * Get the user's timezone.
     * Episode 7: Timezone handling
     */
    public function getTimezoneAttribute($value): string
    {
        return $value ?? 'UTC';
    }

    /**
     * Check if user is an admin.
     * Episode 3: Authorization checks
     */
    public function isAdmin(): bool
    {
        return $this->is_admin === true;
    }

    /**
     * Get user's orders.
     * Episode 2: N+1 - Relationship for eager loading
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get user's cart.
     */
    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class);
    }
}
