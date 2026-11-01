<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Route prefix
    |--------------------------------------------------------------------------
    |
    | All package routes are registered under this prefix, e.g. /hr.
    |
    */

    'route_prefix' => 'hr',

    /*
    |--------------------------------------------------------------------------
    | Layout
    |--------------------------------------------------------------------------
    |
    | Name of a Blade layout in the HOST application to wrap the HR pages in.
    | When a view with this name exists, the package auto-extends it (so HR
    | pages inherit the host's Bootstrap/Tailwind theme). Set to false to
    | always use the package's built-in standalone layout.
    |
    | The host layout must @yield('content') to render the page markup.
    |
    */

    'layout' => 'layouts.app',

    /*
    |--------------------------------------------------------------------------
    | Pagination view
    |--------------------------------------------------------------------------
    |
    | Blade pagination view passed to LengthAwarePaginator::links(). Laravel
    | ships 'pagination::bootstrap-4', 'pagination::bootstrap-5' and
    | 'pagination::tailwind'. Leave null for the framework default.
    |
    */

    'pagination_view' => null,

    /*
    |--------------------------------------------------------------------------
    | Route middleware
    |--------------------------------------------------------------------------
    |
    | Middleware applied to the route group before the package's own
    | "ensure-hr-role" middleware.
    |
    */

    'middleware' => ['web', 'auth'],

    /*
    |--------------------------------------------------------------------------
    | Working hours
    |--------------------------------------------------------------------------
    |
    | Used by the attendance clock to auto-compute the "late" status.
    |
    */

    'working_hours' => [
        'start' => '09:00',
        'end' => '17:00',
    ],

    /*
    |--------------------------------------------------------------------------
    | Late grace period
    |--------------------------------------------------------------------------
    |
    | Minutes after the working-hours start that still count as "present".
    |
    */

    'late_grace_minutes' => 15,

    /*
    |--------------------------------------------------------------------------
    | Roles
    |--------------------------------------------------------------------------
    |
    | Employee roles used for authorization.
    |
    */

    'roles' => [
        'employee' => 'employee',
        'manager' => 'manager',
        'admin' => 'admin',
    ],

    /*
    |--------------------------------------------------------------------------
    | Roles source
    |--------------------------------------------------------------------------
    |
    | Where HR roles are read from:
    |
    | - "employee": the employees.role column (default, no extra packages)
    | - "spatie":   spatie/laravel-permission roles assigned to the user model
    |
    | With "spatie", employees.role is still kept in sync for sorting and
    | filtering, and rows whose user has no mapped Spatie role yet fall back
    | to the column. Run "php artisan hr:sync-roles" after switching.
    |
    */

    'roles_source' => env('HR_ROLES_SOURCE', 'employee'),

    /*
    |--------------------------------------------------------------------------
    | Spatie role mapping
    |--------------------------------------------------------------------------
    |
    | Maps each HR role to the Spatie role name. Only used when the roles
    | source above is "spatie". Mapped roles are managed by this package.
    |
    */

    'spatie' => [
        'role_map' => [
            'employee' => 'employee',
            'manager' => 'manager',
            'admin' => 'admin',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Leave types
    |--------------------------------------------------------------------------
    |
    | The leave types an employee may apply for.
    |
    */

    'leave_types' => ['sick', 'casual', 'annual', 'unpaid'],

    /*
    |--------------------------------------------------------------------------
    | Attendance corrections
    |--------------------------------------------------------------------------
    |
    | allow_within_days limits how old an attendance record may be before an
    | employee can request a correction. Set to 0 or null for no limit.
    |
    */

    'corrections' => [
        'allow_within_days' => 7,
    ],

    /*
    |--------------------------------------------------------------------------
    | Quota fallback
    |--------------------------------------------------------------------------
    |
    | These values are ONLY used when no leave settlement row exists in the
    | database. Once you create settlements via /hr/settlements, those win.
    |
    | "unpaid" => -1 means unlimited (never counted against the quota).
    |
    */

    'quota' => [
        'total_days' => 20,
        'probation_cap' => 2,
        'unpaid' => -1,
    ],

];