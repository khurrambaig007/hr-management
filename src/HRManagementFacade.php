<?php

namespace Khurrambaig\HRManagement;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Khurrambaig\HRManagement\Services\AccrualService accrual()
 * @method static \Khurrambaig\HRManagement\Services\LeaveService leaves()
 * @method static \Khurrambaig\HRManagement\Services\AttendanceService attendance()
 * @method static \Khurrambaig\HRManagement\Services\AttendanceCorrectionService corrections()
 */
class HRManagementFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'hr-management';
    }
}