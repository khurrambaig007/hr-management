# Plan: `khurrambaig/hr-management`

A reusable **Laravel 11+ package** providing HR management (departments, employees, attendance, leaves, and leave settlements) with role-based visibility, Blade views, and its own database tables.

## 1. Goals

- Installable via Composer in any Laravel 11+ application.
- Ships its own migrations, models, controllers, routes, views, config, middleware, and console commands.
- Company-wide **leave settlement CRUD** (one policy per year) drives leave quotas.
- **Role-based** access: `employee` (self only), `manager` (self + approvals), `admin` (full).
- Uses the host app's built-in auth (Laravel Breeze recommended); package adds `auth` + `EnsureHRRole` middleware.

## 2. Tech Constraints

| Item | Value |
|---|---|
| PHP | `^8.2` |
| Laravel | `^11.0` (illuminate/support) |
| Dev branch | `dev-main` |
| Min stability | `dev` |
| Namespace | `Khurrambaig\HRManagement` |
| PSR-4 source dir | `src/` |

## 3. Package Structure

```
khurrambaig/hr-management/
├── composer.json
├── .gitignore
├── README.md
├── config/
│   └── hr-management.php
├── database/
│   ├── migrations/
│   │   ├── create_departments_table.php
│   │   ├── create_employees_table.php
│   │   ├── create_attendances_table.php
│   │   ├── create_leaves_table.php
│   │   ├── create_leave_settlements_table.php
│   │   └── create_leave_balances_table.php
│   └── seeders/
│       └── HRDepartmentSeeder.php
├── resources/views/
│   ├── layouts/
│   │   ├── app.blade.php
│   │   └── components/
│   │       ├── nav.blade.php
│   │       └── alert.blade.php
│   ├── no-profile.blade.php
│   ├── dashboard.blade.php
│   ├── employees/
│   │   ├── index.blade.php
│   │   ├── create.blade.php
│   │   ├── edit.blade.php
│   │   └── show.blade.php
│   ├── attendance/
│   │   ├── index.blade.php
│   │   └── report.blade.php
│   ├── leaves/
│   │   ├── index.blade.php
│   │   ├── create.blade.php
│   │   ├── show.blade.php
│   │   └── balance.blade.php
│   └── settlements/
│       ├── index.blade.php
│       ├── create.blade.php
│       └── edit.blade.php
├── routes/
│   └── web.php
└── src/
    ├── Console/
    │   └── Commands/
    │       ├── CheckPermanency.php
    │       └── GrantAnniversaryQuota.php
    ├── Http/
    │   ├── Controllers/
    │   │   ├── DashboardController.php
    │   │   ├── EmployeeController.php
    │   │   ├── AttendanceController.php
    │   │   ├── LeaveController.php
    │   │   └── LeaveSettlementController.php
    │   ├── Middleware/
    │   │   └── EnsureHRRole.php
    │   └── Traits/
    │       └── ProvidesEmployeeContext.php
    ├── Models/
    │   ├── Department.php
    │   ├── Employee.php
    │   ├── Attendance.php
    │   ├── Leave.php
    │   ├── LeaveSettlement.php
    │   └── LeaveBalance.php
    ├── Services/
    │   ├── AccrualService.php
    │   ├── AttendanceService.php
    │   ├── LeaveService.php
    │   └── EmployeeService.php
    ├── HRManagement.php
    ├── HRManagementFacade.php
    └── HRManagementServiceProvider.php
```

## 4. Database Schema

### `departments`
| Column | Type | Notes |
|---|---|---|
| id | bigint (PK) | auto |
| name | string | unique |
| description | text | nullable |
| timestamps | | |

### `employees`
Belongs to `User` and `Department`.
| Column | Type | Notes |
|---|---|---|
| id | bigint (PK) | auto |
| user_id | bigint FK → users | unique, cascade delete |
| department_id | bigint FK → departments | nullable, nullOnDelete |
| position | string | nullable |
| salary | decimal(12,2) | nullable |
| phone | string | nullable |
| address | text | nullable |
| hire_date | date | nullable |
| probation_end_date | date | nullable |
| is_permanent | boolean default false | set true when probation passes |
| role | enum('employee','manager','admin') | default employee |
| timestamps | | |

### `attendances`
Belongs to `User` and `Employee`.
| Column | Type | Notes |
|---|---|---|
| id | bigint (PK) | |
| user_id | bigint FK → users | |
| date | date | |
| clock_in | timestamp | nullable |
| clock_out | timestamp | nullable |
| status | enum('present','absent','late','half_day') | default present |
| notes | text | nullable |
| timestamps | | |
| — | unique(user_id, date) | one record per day |

### `leaves`
Belongs to `User` and `Employee`; `approved_by` = admin/manager user.
| Column | Type | Notes |
|---|---|---|
| id | bigint (PK) | |
| user_id | bigint FK → users | |
| type | enum('sick','casual','annual','unpaid') | |
| start_date | date | |
| end_date | date | |
| days | decimal | computed (end - start + 1) |
| reason | text | |
| status | enum('pending','approved','rejected') | default pending |
| approved_by | bigint FK → users | nullable |
| timestamps | | |

### `leave_settlements` (company-wide policy per year — admin CRUD)
| Column | Type | Notes |
|---|---|---|
| id | bigint (PK) | |
| year | integer | unique |
| title | string | nullable, e.g. "FY 2026-27" |
| total_days | decimal(8,2) | default 20 |
| probation_cap | decimal(8,2) | default 2 |
| is_active | boolean | default true |
| notes | text | nullable |
| timestamps | | |

### `leave_balances` (ledger — one row per employee leave-year)
| Column | Type | Notes |
|---|---|---|
| id | bigint (PK) | |
| user_id | bigint FK → users | |
| year_start | date | hire-anniversary window start |
| year_end | date | window end (start + 12 months - 1 day) |
| total_days | decimal(8,2) | 20 once permanent |
| used_days | decimal(8,2) | incremented on approval |
| timestamps | | |
| — | unique(user_id, year_start) | |

### `attendance_corrections` (clock in/out correction requests)
| Column | Type | Notes |
|---|---|---|
| id | bigint (PK) | |
| attendance_id | FK → attendances | cascade delete, unique |
| user_id | FK → users | requester |
| requested_clock_in / requested_clock_out | timestamp nullable | the fixed times (one may be null) |
| old_clock_in / old_clock_out | timestamp nullable | snapshot at request time (audit) |
| reason | text | |
| status | enum('pending','approved','rejected') | default pending |
| approved_by | FK → users nullable | |
| reviewed_at | timestamp nullable | |
| timestamps | | |

## 4b. Attendance Corrections Feature

- Employees request corrections for their **own** records only; at least one clock value must change/fill in.
- One **pending** correction per attendance record; records older than `corrections.allow_within_days` (default 7) can't be corrected.
- **Approval** updates the attendance row, recomputes `status` (present/late/half_day) via shared logic in `AttendanceService`, and appends a `[Correction] old → new` note.
- **Rejection** leaves the record untouched; audit trail preserved in the corrections row.
- Admins can also **directly edit** any attendance row (`Attendance@edit/update`).
- Routes: `hr.attendance.corrections.index/create/store/show/approve/reject` + `hr.attendance.edit/update`.
- Services: `AttendanceCorrectionService`; model `AttendanceCorrection`; controller `AttendanceCorrectionController`; views under `attendance/corrections/` + `attendance/edit.blade.php`. Dashboard shows a pending-corrections widget for managers.

## 5. Business Rules (Locked)

1. **Leave year** = each employee's **hire-anniversary 12-month window**. New quota is granted automatically at the start of each new window.
2. **Quota source** = the active `leave_settlements` row for that year (DB), **not** config. Config is only a fallback.
3. **First leave year:**
   - During probation: **max 2 leaves** (`probation_cap`).
   - After confirmation (`is_permanent = true`): **unlocks to 20** (`total_days`); any probation leaves already used count against the 20 (2 used → 18 remaining).
4. **Subsequent years:** full **20** from day one of the window.
5. **Date-based charging:** each leave date is charged to the leave-year window it falls in. A leave spanning the anniversary boundary is **split across quotas** (e.g., Jun 29–Jul 2 → 2 days old window + 2 days new window).
6. **No rollover, no cash payout.** Unused days expire at window end.
7. **Unpaid** leave is unlimited and never counted against the quota.
8. **Probation:** any leave type may be taken, but total is capped per the active settlement's `probation_cap`.
9. **Over-application blocked:** `LeaveService::apply()` rejects a request if it would exceed the remaining balance for the affected window(s).

## 6. Access Control (`EnsureHRRole`)

- Requires `auth`.
- Requires an `employee` record for the user. If missing → friendly "ask your admin to create your employee profile" view instead of the clock-in page.
- Roles gate actions:
  - `employee` — dashboard, own attendance (clock-in/out), own leaves (apply/list/show), own balance.
  - `manager` — everything above + approval/rejection of pending leaves + attendance reports.
  - `admin` — everything + employee CRUD + manual attendance + settlements CRUD.

## 7. Routes (prefix `/hr`, middleware `web` + `auth` + `EnsureHRRole`)

| Method | URI | Controller@action | Roles |
|---|---|---|---|
| GET | `/hr` | Dashboard@index | all |
| GET | `/hr/attendance` | Attendance@index | all (self), admin/manager (all) |
| POST | `/hr/attendance/clock-in` | Attendance@clockIn | all |
| POST | `/hr/attendance/clock-out` | Attendance@clockOut | all |
| POST | `/hr/attendance/store` | Attendance@store | admin |
| GET | `/hr/attendance/report` | Attendance@report | admin/manager |
| GET | `/hr/leaves` | Leave@index | all (self) |
| GET | `/hr/leaves/create` | Leave@create | all |
| POST | `/hr/leaves` | Leave@store | all |
| GET | `/hr/leaves/balance` | Leave@balance | all |
| GET | `/hr/leaves/{leave}` | Leave@show | self or admin/manager |
| POST | `/hr/leaves/{leave}/approve` | Leave@approve | admin/manager |
| POST | `/hr/leaves/{leave}/reject` | Leave@reject | admin/manager |
| GET | `/hr/employees` | Employee@index | admin |
| GET | `/hr/employees/create` | Employee@create | admin |
| POST | `/hr/employees` | Employee@store | admin |
| GET | `/hr/employees/{employee}` | Employee@show | admin |
| GET | `/hr/employees/{employee}/edit` | Employee@edit | admin |
| PUT/PATCH | `/hr/employees/{employee}` | Employee@update | admin |
| DELETE | `/hr/employees/{employee}` | Employee@destroy | admin |
| GET | `/hr/settlements` | LeaveSettlement@index | admin |
| GET | `/hr/settlements/create` | LeaveSettlement@create | admin |
| POST | `/hr/settlements` | LeaveSettlement@store | admin |
| GET | `/hr/settlements/{settlement}/edit` | LeaveSettlement@edit | admin |
| PUT/PATCH | `/hr/settlements/{settlement}` | LeaveSettlement@update | admin |
| DELETE | `/hr/settlements/{settlement}` | LeaveSettlement@destroy | admin |

## 8. Services & Logic

- **AccrualService** — `currentWindow()`, `windowContaining()`, `nextWindow()`, `settlementFor()`, `allowanceFor()`, `getOrCreateBalance()`, `syncQuota()`, `splitAcrossWindows()`.
- **AttendanceService** — `clockIn()`, `clockOut()`, `todayFor()`, `manualStore()`, `monthlyReport()`.
- **LeaveService** — `apply()` (validates balance, probation cap, overlap, split-charging), `approve()` / `reject()` (sets `approved_by`, bumps `used_days` on the correct balance row(s)), `balanceFor()`.
- **EmployeeService** — store/update/destroy with guards (no deleting self, no removing the last admin), permanency provisioning.

## 9. Console Commands

| Command | Purpose |
|---|---|
| `hr:check-permanency` | Auto-confirms employees whose `probation_end_date` has passed (sets `is_permanent = true`, unlocks quota). |
| `hr:grant-anniversary-quota` | Creates the current + next window's `leave_balances` rows for every employee (or a given user). |

Both are registered in the service provider and documented for scheduler use in the host app.

## 10. Config (`config/hr-management.php`)

```php
return [
    'route_prefix' => 'hr',
    'middleware'   => ['web', 'auth'],
    'working_hours' => ['start' => '09:00', 'end' => '17:00'],
    'late_grace_minutes' => 15,
    'roles' => ['employee', 'manager', 'admin'],
    'leave_types' => ['sick', 'casual', 'annual', 'unpaid'],
    'quota' => [               // fallback only; DB settlements win
        'total_days'   => 20,
        'probation_cap' => 2,
        'unpaid'       => -1,  // -1 = unlimited
    ],
];
```

## 11. Service Provider & Facade

- `HRManagementServiceProvider` — merge/load config, load views (+ view namespace `hr`), register routes, register middleware alias (`ensure-hr-role`), register commands, publish tags.
- `HRManagement` (facade + singleton) — thin accessor to `AccrualService`, `LeaveService`, `AttendanceService`.
- **Publish tags:**
  - `hr-management-config` → config
  - `hr-management-migrations` → migrations
  - `hr-management-views` → views
  - `hr-management-seeds` → seeder

## 12. Views

- Self-contained layout (`hr::layouts.app`) with nav, alerts, and responsive tables; no dependency on host-app styling.
- Dashboard shows a **leave balance widget** (remaining / total, progress bar) plus today's attendance status + quick actions (clock in/out, apply leave).
- Employee-facing views only expose the user's own records; admin/manager views include filters (department, date range, month).
- CSRF + validation error display on forms, pagination on index lists.

## 13. Installation & Usage (host app)

```bash
composer require khurrambaig/hr-management:@dev
php artisan vendor:publish --tag=hr-management-migrations
php artisan migrate
php artisan vendor:publish --tag=hr-management-config
php artisan vendor:publish --tag=hr-management-views   # optional overrides
php artisan vendor:publish --tag=hr-management-seeds && php artisan db:seed --class=HRDepartmentSeeder
```

Then:
```bash
php artisan hr:check-permanency      # schedule daily (optional)
php artisan hr:grant-anniversary-quota
```
Visit `/hr` once logged in.

## 14. Build Order

1. `composer.json`, `.gitignore`, `README.md`
2. `config/hr-management.php`
3. Six migrations
4. Six models + relationships/scopes
5. Four services
6. Two console commands
7. Five controllers
8. `EnsureHRRole` middleware
9. `routes/web.php`
10. Blade views
11. Service provider + facade
12. Verify: `composer dump-autoload`, then smoke-test in a throwaway Laravel 11 app (install, migrate, seed, hit `/hr`, run commands)

## 15. Open Items / Assumptions

- Laravel Breeze (or equivalent) is the assumed auth provider in host apps; the package requires a users table with `email`/`password`.
- Employee records are created **manually by admins** (no auto-provisioning on clock-in).
- No multi-company/tenant support.
- Decimal day counts allowed (e.g., half-day leaves) though not currently surfaced in the UI — can be added later.