<?php

namespace Khurrambaig\HRManagement\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Khurrambaig\HRManagement\Http\Traits\ProvidesEmployeeContext;
use Khurrambaig\HRManagement\Models\Attendance;
use Khurrambaig\HRManagement\Services\AttendanceService;

class AttendanceController extends BaseController
{
    use ProvidesEmployeeContext;

    public function __construct(protected AttendanceService $attendanceService)
    {
    }

    public function index()
    {
        $employee = $this->requiresEmployeeRecord();

        if ($employee->canManageTeam()) {
            $attendances = Attendance::query()
                ->with('employee.user')
                ->when(request('date'), fn ($query, $date) => $query->where('date', $date))
                ->latest('date')
                ->paginate(20)
                ->withQueryString();
        } else {
            $attendances = Attendance::query()
                ->mine($employee->user_id)
                ->when(request('date'), fn ($query, $date) => $query->where('date', $date))
                ->latest('date')
                ->paginate(20)
                ->withQueryString();
        }

        $today = $this->attendanceService->todayFor($employee->user_id);

        $employeesList = $employee->isAdmin()
            ? \Khurrambaig\HRManagement\Models\Employee::query()->with('user')->orderBy('role')->get()
            : collect();

        return view('hr::attendance.index', compact('attendances', 'today', 'employee', 'employeesList'));
    }

    public function clockIn()
    {
        $employee = $this->requiresEmployeeRecord();

        try {
            $this->attendanceService->clockIn($employee->user_id);
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        return redirect()->route('hr.attendance.index')->with('success', 'You have clocked in.');
    }

    public function clockOut()
    {
        $employee = $this->requiresEmployeeRecord();

        try {
            $this->attendanceService->clockOut($employee->user_id);
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        return redirect()->route('hr.attendance.index')->with('success', 'You have clocked out.');
    }

    public function store()
    {
        $this->requireAdmin();

        $data = request()->validate([
            'user_id' => ['required', 'exists:employees,user_id'],
            'date' => ['required', 'date', Rule::unique('attendances', 'date')->where('user_id', request('user_id'))],
            'clock_in' => ['nullable', 'date'],
            'clock_out' => ['nullable', 'date', 'after:clock_in'],
            'status' => ['nullable', Rule::in([Attendance::STATUS_PRESENT, Attendance::STATUS_ABSENT, Attendance::STATUS_LATE, Attendance::STATUS_HALF_DAY])],
            'notes' => ['nullable', 'string'],
        ]);

        if (! ($data['clock_in'] ?? null)) {
            unset($data['clock_in']);
        }
        if (! ($data['clock_out'] ?? null)) {
            unset($data['clock_out']);
        }

        $this->attendanceService->manualStore($data);

        return redirect()->route('hr.attendance.index')->with('success', 'Attendance recorded.');
    }

    public function edit(Attendance $attendance)
    {
        $this->requireAdmin();

        $attendance->load('employee.user');

        $employeesList = \Khurrambaig\HRManagement\Models\Employee::query()->with('user')->orderBy('role')->get();

        return view('hr::attendance.edit', compact('attendance', 'employeesList'));
    }

    public function update(Attendance $attendance)
    {
        $this->requireAdmin();

        $data = request()->validate([
            'user_id' => ['required', 'exists:employees,user_id'],
            'date' => ['required', 'date', Rule::unique('attendances', 'date')->where('user_id', request('user_id'))->ignore($attendance->id)],
            'clock_in' => ['nullable', 'date'],
            'clock_out' => ['nullable', 'date', 'after:clock_in'],
            'status' => ['nullable', Rule::in([Attendance::STATUS_PRESENT, Attendance::STATUS_ABSENT, Attendance::STATUS_LATE, Attendance::STATUS_HALF_DAY])],
            'notes' => ['nullable', 'string'],
        ]);

        if (blank($data['clock_in'] ?? null)) {
            $data['clock_in'] = null;
        }
        if (blank($data['clock_out'] ?? null)) {
            $data['clock_out'] = null;
        }

        try {
            $this->attendanceService->update($attendance, $data, blank($data['status'] ?? null));
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput();
        }

        return redirect()->route('hr.attendance.index')->with('success', 'Attendance updated.');
    }

    public function report()
    {
        $this->requireManager();

        $employee = $this->requiresEmployeeRecord();
        $month = request('month') ?: now()->format('Y-m');

        $rows = $this->attendanceService->monthlyReport($month);

        $employees = \Khurrambaig\HRManagement\Models\Employee::query()->with('user')->orderBy('role')->get();

        return view('hr::attendance.report', compact('rows', 'month', 'employees', 'employee'));
    }
}