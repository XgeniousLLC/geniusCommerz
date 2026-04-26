<?php

namespace App\Models;

use App\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticatable
{
    use Auditable, HasFactory, HasRoles, Notifiable;

    protected string $guard_name = 'admin';

    protected $fillable = [
        'uuid',
        'name',
        'email',
        'password',
        'is_active',
        'role',
        'invite_token',
        'must_reset_password',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'invite_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'must_reset_password' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Admin $admin) {
            if (empty($admin->uuid)) {
                $admin->uuid = (string) Str::uuid();
            }
        });
    }

    public function createdPages()
    {
        return $this->hasMany(Page::class, 'created_by');
    }

    public function updatedPages()
    {
        return $this->hasMany(Page::class, 'updated_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
