# Changelog

All notable changes to `khurrambaig/hr-management` are documented here.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.2.0] - 2026-11-01

### Added

- Optional `spatie/laravel-permission` integration via `roles_source => 'spatie'`.
- Configurable Spatie role map and automatic role sync when employees are created or updated.
- `hr:sync-roles` command to mirror existing `employees.role` values to Spatie roles.
- Role filtering and last-admin protection respect the configured role source.

## [1.1.0] - 2026-11-01

### Added

- Laravel 12.x and 13.x support (Laravel 13 requires PHP 8.3+).
- CI matrix covering Laravel 11, 12 and 13.

No source code changes; the constraints were widened after the full test suite passed on every supported major.

## [1.0.0] - 2026-11-01

### Added

- Department and employee management with role-based visibility.
- Attendance clock-in/clock-out, automatic `late`/`half_day` status, manual entry and monthly reports.
- Attendance correction requests with manager/admin approval and audit notes.
- Leave applications, approvals, rejections and balance tracking.
- Leave settlement policies (`total_days` / `probation_cap`) managed per year.
- Hire-anniversary leave accrual, probation caps and date-based charging across leave years.
- Configurable route prefix, middleware, layout integration, pagination view and working hours.
- Artisan commands: `hr:grant-anniversary-quota` and `hr:check-permanency`.
- Publishable migrations, config, seeders and views.

[1.0.0]: https://github.com/khurrambaig007/hr-management/releases/tag/v1.0.0
