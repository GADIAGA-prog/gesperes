<?php

namespace App\Models;

use App\Support\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, SoftDeletes, Auditable;

    protected $fillable = [
        'name', 'email', 'password', 'actif', 'region', 'structure_id',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'actif' => 'boolean',
        ];
    }

    public function structure()
    {
        return $this->belongsTo(Structure::class);
    }

    public function agent()
    {
        return $this->hasOne(Agent::class);
    }

    /** Empêche de cibler les comptes super-admin dans les listes sensibles. */
    public function estSuperAdmin(): bool
    {
        return $this->hasRole(\App\Enums\RoleName::SUPER_ADMIN->value);
    }
}
