<?php

namespace Khurrambaig\HRManagement\Models;

use Illuminate\Database\Eloquent\Builder;
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
        return $this->roleName() === self::ROLE_ADMIN;
    }

    public function isManager(): bool
    {
        return $this->roleName() === self::ROLE_MANAGER;
    }

    public function isEmployee(): bool
    {
        return $this->roleName() === self::ROLE_EMPLOYEE;
    }

    /**
     * Resolve the HR role, preferring Spatie roles when configured.
     */
    public function roleName(): string
    {
        if (! $this->usesSpatieRoles()) {
            return $this->role;
        }

        $assigned = $this->user->getRoleNames()->all();

        // Legacy rows may not have been synced yet: fall back to the column.
        if ($assigned === []) {
            return $this->role;
        }

        foreach ([self::ROLE_ADMIN, self::ROLE_MANAGER, self::ROLE_EMPLOYEE] as $hrRole) {
            if (in_array(static::spatieRoleName($hrRole), $assigned, true)) {
                return $hrRole;
            }
        }

        return $this->role;
    }

    public function usesSpatieRoles(): bool
    {
        if (! static::spatieEnabled()) {
            return false;
        }

        $user = $this->user;

        return $user !== null && method_exists($user, 'getRoleNames');
    }

    public static function spatieEnabled(): bool
    {
        return config('hr-management.roles_source', 'employee') === 'spatie'
            && class_exists(\Spatie\Permission\PermissionServiceProvider::class);
    }

    /**
     * @return array<string, string> HR role => Spatie role name
     */
    public static function roleMap(): array
    {
        $configured = config('hr-management.spatie.role_map', []);

        return array_merge([
            self::ROLE_EMPLOYEE => self::ROLE_EMPLOYEE,
            self::ROLE_MANAGER => self::ROLE_MANAGER,
            self::ROLE_ADMIN => self::ROLE_ADMIN,
        ], is_array($configured) ? $configured : []);
    }

    public static function spatieRoleName(string $hrRole): string
    {
        return static::roleMap()[$hrRole] ?? $hrRole;
    }

    /**
     * Replace the mapped HR roles on the employee's user with the given one.
     *
     * Roles outside the map are left untouched.
     */
    public static function syncSpatieRole(self $employee, string $hrRole): void
    {
        if (! static::spatieEnabled()) {
            return;
        }

        $user = $employee->user;

        if (! $user
            || ! method_exists($user, 'assignRole')
            || ! method_exists($user, 'removeRole')
            || ! method_exists($user, 'hasRole')) {
            return;
        }

        foreach (array_unique(array_values(static::roleMap())) as $roleName) {
            if ($user->hasRole($roleName)) {
                $user->removeRole($roleName);
            }
        }

        $roleModel = 'Spatie\\Permission\\Models\\Role';

        $user->assignRole($roleModel::findOrCreate(static::spatieRoleName($hrRole)));
    }

    /**
     * Filter employees by HR role, taking the configured source into account.
     */
    public function scopeWhereHrRole(Builder $query, string $hrRole): Builder
    {
        if (! static::spatieEnabled()) {
            return $query->where('role', $hrRole);
        }

        $userModel = config('auth.providers.users.model', \App\Models\User::class);

        if (! method_exists($userModel, 'getRoleNames')) {
            return $query->where('role', $hrRole);
        }

        $spatieRole = static::spatieRoleName($hrRole);
        $mappedRoles = array_values(array_unique(static::roleMap()));
        $guard = class_exists(\Spatie\Permission\Guard::class)
            ? \Spatie\Permission\Guard::getDefaultName($userModel)
            : config('auth.defaults.guard', 'web');

        return $query->where(function (Builder $query) use ($spatieRole, $guard, $hrRole, $mappedRoles) {
            $query->whereHas('user.roles', fn (Builder $roles) => $roles
                ->where('name', $spatieRole)
                ->where('guard_name', $guard))
                ->orWhere(fn (Builder $user) => $user
                    ->where('role', $hrRole)
                    ->whereDoesntHave('user.roles', fn (Builder $roles) => $roles
                        ->whereIn('name', $mappedRoles)
                        ->where('guard_name', $guard)));
        });
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