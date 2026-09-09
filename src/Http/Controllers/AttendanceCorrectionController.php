<?php

namespace Khurrambaig\HRManagement\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Validation\ValidationException;
use Khurrambaig\HRManagement\Http\Traits\ProvidesEmployeeContext;
use Khurrambaig\HRManagement\Models\AttendanceCorrection;
use Khurrambaig\HRManagement\Services\AttendanceCorrectionService;

class AttendanceCorrectionController extends BaseController
{
    use ProvidesEmployeeContext;

    public function __construct(protected AttendanceCorrectionService $correctionService)
    {
    }

    public function index()
    {
        $employee = $this->requiresEmployeeRecord();

        $corrections = AttendanceCorrection::query()
            ->mine($employee->user_id)
            ->with(['attendance'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $pendingQueue = $employee->canManageTeam()
            ? AttendanceCorrection::query()
                ->pending()
                ->with(['attendance.employee.user', 'user'])
                ->latest()
                ->get()
            : collect();

        return view('hr::attendance.corrections.index', compact('corrections', 'pendingQueue', 'employee'));
    }

    public function create()
    {
        $employee = $this->requiresEmployeeRecord();

        $attendances = $this->correctionService->correctableFor($employee->user_id);

        if ($attendances->isEmpty()) {
            return redirect()->route('hr.attendance.corrections.index')
                ->with('error', 'There are no attendance records you can currently correct.');
        }

        return view('hr::attendance.corrections.create', compact('attendances', 'employee'));
    }

    public function store()
    {
        $employee = $this->requiresEmployeeRecord();

        $data = request()->validate([
            'attendance_id' => ['required', 'exists:attendances,id'],
            'requested_clock_in' => ['nullable', 'date'],
            'requested_clock_out' => ['nullable', 'date'],
            'reason' => ['required', 'string', 'max:2000'],
        ]);

        try {
            $this->correctionService->apply($employee->user_id, $data);
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput();
        }

        return redirect()->route('hr.attendance.corrections.index')->with('success', 'Correction request submitted for approval.');
    }

    public function show(AttendanceCorrection $correction)
    {
        $employee = $this->requiresEmployeeRecord();

        abort_unless(
            $correction->user_id === $employee->user_id || $employee->canManageTeam(),
            403,
            'You do not have permission to view this correction.'
        );

        $correction->load(['attendance.employee.user', 'user', 'approvedBy']);

        return view('hr::attendance.corrections.show', compact('correction', 'employee'));
    }

    public function approve(AttendanceCorrection $correction)
    {
        $this->requireManager();

        try {
            $this->correctionService->approve($correction, auth()->id());
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        return back()->with('success', 'Correction approved and attendance updated.');
    }

    public function reject(AttendanceCorrection $correction)
    {
        $this->requireManager();

        try {
            $this->correctionService->reject($correction, auth()->id());
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        return back()->with('success', 'Correction request rejected.');
    }
}