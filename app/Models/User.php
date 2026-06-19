<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'panelist_name',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'password' => 'hashed',
        'role' => 'string',
    ];

    /**
     * Check if the user is a super user.
     */
    public function isSuper(): bool
    {
        return $this->role === 'super';
    }

    /**
     * Check if the user is a panelist.
     */
    public function isPanelist(): bool
    {
        return $this->role === 'panelist';
    }

    /**
     * Get the panel scores assigned by this panelist.
     */
    public function panelScores(): HasMany
    {
        return $this->hasMany(PanelScore::class, 'panelist_id');
    }
}
