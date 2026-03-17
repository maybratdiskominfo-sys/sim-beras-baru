<?php

namespace App\Models;

use Filament\Panel;
use App\Models\Employee;
use App\Models\Department;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\HasTenants;
use Filament\Models\Contracts\FilamentUser;
use BezhanSalleh\FilamentShield\Traits\HasPanelShield;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable implements FilamentUser, HasTenants, HasAvatar
{
    // HasPanelShield memungkinkan Filament Shield mengatur akses panel secara otomatis
    use HasFactory, Notifiable, HasRoles, HasPanelShield;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
        'employee_id', 
        'department_id',
        'avatar_url',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    // --- FILAMENT TENANCY LOGIC ---

    public function getTenants(Panel $panel): Collection
    {
        // Super Admin dapat berpindah-pindah ke semua Dinas/OPD
        if ($this->hasRole('super_admin')) {
            return Department::all();
        }

        // Admin OPD atau User biasa hanya melihat Department tempat mereka ditugaskan
        return $this->departments;
    }

    public function canAccessTenant(Model $tenant): bool
    {
        if ($this->hasRole('super_admin')) {
            return true;
        }

        return $this->departments->contains($tenant);
    }

    // --- FILAMENT ACCESS & AVATAR ---

    public function canAccessPanel(Panel $panel): bool
    {
        // Akses panel dikontrol oleh role di Shield, 
        // namun pastikan akun juga dalam status aktif secara sistem.
        return (bool) $this->is_active;
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar_url ? Storage::url($this->avatar_url) : null;
    }

    
    // --- RELATIONSHIPS ---

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class, 'user_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class);
    }
}