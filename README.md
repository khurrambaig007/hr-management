# hr-management

[![Latest Version](https://img.shields.io/packagist/v/khurrambaig/hr-management.svg)](https://packagist.org/packages/khurrambaig/hr-management)
[![Total Downloads](https://img.shields.io/packagist/dt/khurrambaig/hr-management.svg)](https://packagist.org/packages/khurrambaig/hr-management)
[![License](https://img.shields.io/packagist/l/khurrambaig/hr-management.svg)](https://github.com/khurrambaig007/hr-management/blob/dev-main/LICENSE)

HR management package for Laravel 11+: departments, employees, attendance, leaves and annual leave settlements.

## Requirements

- PHP `^8.2`
- Laravel `^11.0`
- A `users` table (Laravel Breeze or equivalent auth is assumed)

## Installation

```bash
composer require khurrambaig/hr-management
```

To use the latest unreleased code instead of a stable tag:

```bash
composer require khurrambaig/hr-management:@dev
```

### Publish & migrate

```bash
php artisan vendor:publish --tag=hr-management-migrations
php artisan migrate

php artisan vendor:publish --tag=hr-management-config
php artisan vendor:publish --tag=hr-management-seeds
php artisan db:seed --class=HRDepartmentSeeder
```

Views are loaded automatically. If you want to override them in your app:

```bash
php artisan vendor:publish --tag=hr-management-views
```

### Optional: publish all

```bash
php artisan vendor:publish --provider="Khurrambaig\HRManagement\HRManagementServiceProvider"
```

## Quick start

1. Make sure at least one admin has an employee record (create via the UI at `/hr/employees`).
2. Create leave settlement policies for each year at `/hr/settlements`.
3. Visit `/hr` to reach the dashboard.

### Scheduled commands (optional)

```php
// app/Console/Kernel.php (or routes/console.php for Laravel 11)
use Illuminate\Support\Facades\Schedule;

Schedule::command('hr:grant-anniversary-quota')->daily();
Schedule::command('hr:check-permanency')->daily();
```

- `hr:grant-anniversary-quota` — creates the current + next leave-year balance rows for employees.
- `hr:check-permanency` — automatically confirms employees whose probation ended (unlocks full leave quota).
- `hr:grant-anniversary-quota --user=42` — only processes a single user id.

## Features

- **Departments & Employees** — admin-managed records with role-based visibility.
- **Attendance** — clock-in/clock-out with auto `late` / `half_day` status, manual entry and monthly reports.
- **Attendance corrections** — employees request clock-in/out fixes (missing or wrong times), managers/admins approve or reject; approval updates the record, recomputes status and appends an audit note. Admins can also directly edit any attendance row.
- **Leaves** — apply, list, approve/reject, with leave balance tracking.
- **Leave settlements** — company-wide yearly entitlement managed via admin CRUD (`total_days` / `probation_cap`).
- **Leave accrual** — hire-anniversary leave years, probation caps, quota unlocking on confirmation, and date-based charging across year boundaries.

## Roles

| Role | Rights |
|---|---|
| `employee` | own attendance, own leaves, own balance |
| `manager` | employee rights + approve/reject leaves + attendance reports |
| `admin` | manager rights + employee CRUD + manual attendance + settlements CRUD |

## Business rules (current version)

- Leave year = employee's hire-anniversary 12-month window.
- First year: `probation_cap` during probation, unlocks to `total_days` once confirmed.
- Subsequent years: full `total_days` from day one.
- Leave days are charged to the leave-year they fall in; spanning leaves are split across quotas.
- No carry-over, no cash payout. Unpaid leave is unlimited and never counted against the quota.

All numerically configurable values are managed via the leave settlements CRUD, not hardcoded config.

## Attendance corrections

- Employees can request a correction for their **own** attendance records (fill a missing clock-in/out or fix a wrong time). At least one time must change.
- Only one pending correction is allowed per attendance record.
- Records older than the configured window cannot be corrected — `corrections.allow_within_days` (default `7`, `0`/`null` for no limit).
- Managers/admins see a pending queue and can approve or reject. On approval the attendance row is updated, the status recomputed (`present`/`late`/`half_day`), and a `[Correction] old → new` note is appended for audit.
- Admins can also directly edit any attendance row via the Edit action on the attendance list.

## Theming & layout

HR pages auto-detect your app's layout: if a Blade view matching `hr-management.layout` (default `layouts.app`) exists in **your** app, the package extends it — so HR pages inherit your Bootstrap/Tailwind or custom theme and global nav. Otherwise the package renders its own self-contained layout, so the UI always works out of the box.

- Your layout must `@yield('content')` (Laravel convention) for the page markup to appear.
- Set `'layout' => false` to always force the built-in standalone layout.
- In host-layout mode the package's full stylesheet is skipped in favour of a slim scoped one (nav, cards, badges, tables, buttons) that looks acceptable on any theme.
- For deeper customization, publish the views (`hr-management-views`) and edit as needed.

Pagination follows `pagination_view` — set it to `pagination::bootstrap-4`, `pagination::bootstrap-5` or `pagination::tailwind` (any `paginator` view name); `null` (default) uses the Laravel default.

## Configuration reference

`config/hr-management.php` (fallback values only when no settlement row exists):

```php
return [
    'route_prefix' => 'hr',
    'middleware'   => ['web', 'auth'],
    'layout'       => 'layouts.app',   // host layout to wrap in; false = built-in
    'pagination_view' => null,         // 'pagination::bootstrap-5' | 'pagination::tailwind' | null
    'working_hours' => ['start' => '09:00', 'end' => '17:00'],
    'late_grace_minutes' => 15,
    'roles' => ['employee', 'manager', 'admin'],
    'leave_types' => ['sick', 'casual', 'annual', 'unpaid'],
    'corrections' => [
        'allow_within_days' => 7,
    ],
    'quota' => [
        'total_days'   => 20,
        'probation_cap' => 2,
        'unpaid'       => -1,
    ],
];
```

## License

MIT.