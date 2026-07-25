<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_protected',
        'role',
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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_protected' => 'boolean',
        ];
    }

    // Protección a nivel de modelo (Método 4)
    protected static function booted()
    {
        static::deleting(function ($user) {
            // Prevenir eliminación (soft delete) de usuarios protegidos
            if ($user->is_protected) {
                throw new \Exception('No se puede eliminar el administrador protegido.');
            }
        });

        static::forceDeleting(function ($user) {
            // Prevenir eliminación física de usuarios protegidos
            if ($user->is_protected) {
                throw new \Exception('No se puede eliminar físicamente el administrador protegido.');
            }
        });

        static::updating(function ($user) {
            // Prevenir que alguien quite la protección del admin
            if ($user->getOriginal('is_protected') && !$user->is_protected) {
                throw new \Exception('No se puede desproteger al administrador principal.');
            }
        });
    }

    // 📌 Métodos útiles
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isProtected(): bool
    {
        return $this->is_protected;
    }
}
