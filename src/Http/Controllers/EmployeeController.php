<?php

namespace Khurrambaig\HRManagement\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Validation\Rule;
use Khurrambaig\HRManagement\Http\Traits\ProvidesEmployeeContext;
use Khurrambaig\HRManagement\Models\Department;
use Khurrambaig\HRManagement\Models\Employee;
use Khurrambaig\HRManagement\Services\EmployeeService;

class EmployeeController extends BaseController
{
    use ProvidesEmployeeContext;

    public function __construct(protected EmployeeService $employeeService)
    {
    }

    public function index()
    {
        $this->requireAdmin();

        $employees = Employee::query()
            ->with(Employee::spatieEnabled() ? ['user', 'user.roles', 'department'] : ['user', 'department'])
            ->when(request('department_id'), fn ($query, $departmentId) => $query->where('department_id', $departmentId))
            ->when(request('role'), fn ($query, $role) => $query->whereHrRole($role))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $departments = Department::orderBy('name')->get();

        return view('hr::employees.index', compact('employees', 'departments'));
    }

    public function create()
    {
        $this->requireAdmin();

        $departments = Department::orderBy('name')->get();
        $users = $this->availableUsers();

        return view('hr::employees.create', compact('departments', 'users'));
    }

    public function store()
    {
        $this->requireAdmin();

        $userModel = $this->userModel();

        $data = request()->validate([
            'user_id' => ['required', "exists:{$userModel},id", Rule::unique('employees', 'user_id')],
            'department_id' => ['nullable', 'exists:departments,id'],
            'position' => ['nullable', 'string', 'max:255'],
            'salary' => ['nullable', 'numeric', 'min:0'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'hire_date' => ['nullable', 'date'],
            'probation_end_date' => ['nullable', 'date', 'after_or_equal:hire_date'],
            'is_permanent' => ['nullable', 'boolean'],
            'role' => ['required', Rule::in(config('hr-management.roles'))],
        ]);

        $data['is_permanent'] = isset($data['is_permanent']);

        $employee = $this->employeeService->store((int) $data['user_id'], $data);

        return redirect()->route('hr.employees.show', $employee)
            ->with('success', 'Employee profile created.');
    }

    public function show(Employee $employee)
    {
        $this->requireAdmin();

        $employee->load(Employee::spatieEnabled() ? ['user', 'user.roles', 'department'] : ['user', 'department']);

        $attendanceCount = $employee->attendances()->whereYear('date', now()->year)->count();
        $leaveStats = $employee->leaves()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('hr::employees.show', compact('employee', 'attendanceCount', 'leaveStats'));
    }

    public function edit(Employee $employee)
    {
        $this->requireAdmin();

        $departments = Department::orderBy('name')->get();
        $users = $this->availableUsers($employee->user_id);

        return view('hr::employees.edit', compact('employee', 'departments', 'users'));
    }

    public function update(Employee $employee)
    {
        $this->requireAdmin();

        $userModel = $this->userModel();

        $data = request()->validate([
            'user_id' => ['required', "exists:{$userModel},id", Rule::unique('employees', 'user_id')->ignore($employee->id)],
            'department_id' => ['nullable', 'exists:departments,id'],
            'position' => ['nullable', 'string', 'max:255'],
            'salary' => ['nullable', 'numeric', 'min:0'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'hire_date' => ['nullable', 'date'],
            'probation_end_date' => ['nullable', 'date', 'after_or_equal:hire_date'],
            'is_permanent' => ['nullable', 'boolean'],
            'role' => ['required', Rule::in(config('hr-management.roles'))],
        ]);

        $data['is_permanent'] = $data['is_permanent'] ?? false;

        $this->employeeService->update($employee, $data);

        return redirect()->route('hr.employees.show', $employee)
            ->with('success', 'Employee profile updated.');
    }

    public function destroy(Employee $employee)
    {
        $this->requireAdmin();

        $this->employeeService->destroy($employee, auth()->id());

        return redirect()->route('hr.employees.index')
            ->with('success', 'Employee profile deleted.');
    }

    protected function availableUsers(int $exceptUserId = 0): \Illuminate\Support\Collection
    {
        $userModel = $this->userModel();

        $existing = Employee::pluck('user_id')
            ->reject(fn ($id) => (int) $id === $exceptUserId)
            ->all();

        return $userModel::query()
            ->whereNotIn('id', $existing)
            ->orderBy('name')
            ->get();
    }

    protected function userModel(): string
    {
        return config('auth.providers.users.model', \App\Models\User::class);
    }
}