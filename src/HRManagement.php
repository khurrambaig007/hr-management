<?php

namespace Khurrambaig\HRManagement;

use Khurrambaig\HRManagement\Services\AccrualService;
use Khurrambaig\HRManagement\Services\AttendanceCorrectionService;
use Khurrambaig\HRManagement\Services\AttendanceService;
use Khurrambaig\HRManagement\Services\LeaveService;

class HRManagement
{
    public function accrual(): AccrualService
    {
        return app(AccrualService::class);
    }

    public function leaves(): LeaveService
    {
        return app(LeaveService::class);
    }

    public function attendance(): AttendanceService
    {
        return app(AttendanceService::class);
    }

    public function corrections(): AttendanceCorrectionService
    {
        return app(AttendanceCorrectionService::class);
    }
}