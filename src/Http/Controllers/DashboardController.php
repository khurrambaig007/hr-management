<?php

namespace Khurrambaig\HRManagement\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Khurrambaig\HRManagement\Http\Traits\ProvidesEmployeeContext;
use Khurrambaig\HRManagement\Models\Leave;
use Khurrambaig\HRManagement\Services\AttendanceService;
use Khurrambaig\HRManagement\Services\LeaveService;

class DashboardController extends BaseController
{
    use ProvidesEmployeeContext;

    public function __construct(
        protected AttendanceService $attendanceService,
        protected LeaveService $leaveService
    ) {
    }

    public function index()
    {
        $employee = $this->requiresEmployeeRecord();

        $today = $this->attendanceService->todayFor($employee->user_id);

        $balance = $this->leaveService->currentBalance($employee->user_id);

        $recentLeaves = Leave::query()
            ->mine($employee->user_id)
            ->latest()
            ->limit(5)
            ->get();

        $pendingCount = $employee->canManageTeam()
            ? Leave::query()->pending()->count()
            : null;

        $pendingCorrectionCount = $employee->canManageTeam()
            ? \Khurrambaig\HRManagement\Models\AttendanceCorrection::query()->pending()->count()
            : null;

        return view('hr::dashboard', compact(
            'employee',
            'today',
            'balance',
            'recentLeaves',
            'pendingCount',
            'pendingCorrectionCount'
        ));
    }
}