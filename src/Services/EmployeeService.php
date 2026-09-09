<?php

namespace Khurrambaig\HRManagement\Services;

use Illuminate\Validation\ValidationException;
use Khurrambaig\HRManagement\Models\Employee;

class EmployeeService
{
    public function __construct(protected AccrualService $accrual)
    {
    }

    public function store(int $userId, array $data): Employee
    {
        if ($this->existsForUser($userId)) {
            throw ValidationException::withMessages([
                'user_id' => 'This user already has an employee profile.',
            ]);
        }

        $employee = Employee::create(array_merge($data, ['user_id' => $userId]));

        $this->accrual->syncQuota($employee);

        return $employee;
    }

    public function update(Employee $employee, array $data): Employee
    {
        if (! isset($data['is_permanent'])) {
            $data['is_permanent'] = $employee->is_permanent;
        }

        $employee->update($data);

        $this->accrual->syncQuota($employee);

        return $employee;
    }

    /**
     * @throws ValidationException
     */
    public function destroy(Employee $employee, int $actingUserId): void
    {
        if ($employee->user_id === $actingUserId) {
            throw ValidationException::withMessages([
                'employee' => 'You cannot delete your own employee profile.',
            ]);
        }

        if ($employee->isAdmin()) {
            $adminCount = Employee::where('role', Employee::ROLE_ADMIN)->count();

            if ($adminCount <= 1) {
                throw ValidationException::withMessages([
                    'employee' => 'You cannot delete the last admin profile.',
                ]);
            }
        }

        $employee->delete();
    }

    public function existsForUser(int $userId): bool
    {
        return Employee::where('user_id', $userId)->exists();
    }

    public function findByUserId(int $userId): ?Employee
    {
        return Employee::where('user_id', $userId)->first();
    }
}