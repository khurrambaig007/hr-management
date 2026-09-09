<?php

namespace Khurrambaig\HRManagement\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use HasFactory;

    public const ROLE_EMPLOYEE = 'employee';

    public const ROLE_MANAGER = 'manager';

    public const ROLE_ADMIN = 'admin';

    protected $fillable = [
        'user_id',
        'department_id',
        'position',
        'salary',
        'phone',
        'address',
        'hire_date',
        'probation_end_date',
        'is_permanent',
        'role',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'probation_end_date' => 'date',
        'is_permanent' => 'boolean',
        'salary' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model', \App\Models\User::class));
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'user_id', 'user_id');
    }

    public function leaves(): HasMany
    {
        return $this->hasMany(Leave::class, 'user_id', 'user_id');
    }

    public function leaveBalances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class, 'user_id', 'user_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isManager(): bool
    {
        return $this->role === self::ROLE_MANAGER;
    }

    public function isEmployee(): bool
    {
        return $this->role === self::ROLE_EMPLOYEE;
    }

    public function canManageTeam(): bool
    {
        return $this->isAdmin() || $this->isManager();
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->user?->name ?? $this->user?->email ?? sprintf('User #%s', $this->user_id);
    }
}