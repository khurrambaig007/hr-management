<?php

namespace Khurrambaig\HRManagement\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Khurrambaig\HRManagement\Http\Traits\ProvidesEmployeeContext;
use Khurrambaig\HRManagement\Models\Leave;
use Khurrambaig\HRManagement\Services\LeaveService;

class LeaveController extends BaseController
{
    use ProvidesEmployeeContext;

    public function __construct(protected LeaveService $leaveService)
    {
    }

    public function index()
    {
        $employee = $this->requiresEmployeeRecord();

        $leaves = Leave::query()
            ->mine($employee->user_id)
            ->with('approver')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $pendingLeaves = $employee->canManageTeam()
            ? Leave::query()->pending()->with(['employee.user'])->latest()->get()
            : collect();

        return view('hr::leaves.index', compact('leaves', 'pendingLeaves', 'employee'));
    }

    public function create()
    {
        $this->requiresEmployeeRecord();

        return view('hr::leaves.create');
    }

    public function store()
    {
        $employee = $this->requiresEmployeeRecord();

        $data = request()->validate([
            'type' => ['required', Rule::in(config('hr-management.leave_types'))],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'max:2000'],
        ]);

        try {
            $this->leaveService->apply($employee->user_id, $data);
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput();
        }

        return redirect()->route('hr.leaves.index')->with('success', 'Leave request submitted.');
    }

    public function show(Leave $leave)
    {
        $employee = $this->requiresEmployeeRecord();

        abort_unless(
            $leave->user_id === $employee->user_id || $employee->canManageTeam(),
            403,
            'You do not have permission to view this leave.'
        );

        $leave->load(['employee.user', 'approver']);

        return view('hr::leaves.show', compact('leave', 'employee'));
    }

    public function approve(Leave $leave)
    {
        $this->requireManager();

        try {
            $this->leaveService->approve($leave, auth()->id());
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        return back()->with('success', 'Leave approved.');
    }

    public function reject(Leave $leave)
    {
        $this->requireManager();

        try {
            $this->leaveService->reject($leave, auth()->id());
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        return back()->with('success', 'Leave rejected.');
    }

    public function balance()
    {
        $employee = $this->requiresEmployeeRecord();

        $balance = $this->leaveService->currentBalance($employee->user_id);

        $usage = Leave::query()
            ->mine($employee->user_id)
            ->where('status', Leave::STATUS_APPROVED)
            ->get()
            ->groupBy('type')
            ->map->sum('days');

        return view('hr::leaves.balance', compact('employee', 'balance', 'usage'));
    }
}