<?php

use Illuminate\Support\Facades\Route;
use Khurrambaig\HRManagement\Http\Controllers\AttendanceController;
use Khurrambaig\HRManagement\Http\Controllers\AttendanceCorrectionController;
use Khurrambaig\HRManagement\Http\Controllers\DashboardController;
use Khurrambaig\HRManagement\Http\Controllers\EmployeeController;
use Khurrambaig\HRManagement\Http\Controllers\LeaveController;
use Khurrambaig\HRManagement\Http\Controllers\LeaveSettlementController;

Route::prefix(config('hr-management.route_prefix', 'hr'))
    ->middleware(config('hr-management.middleware', ['web', 'auth']))
    ->group(function () {
        Route::get('no-profile', fn () => view('hr::no-profile'))->name('hr.no-profile');

        Route::middleware('ensure-hr-role')->group(function () {
            Route::get('/', DashboardController::class.'@index')->name('hr.dashboard');

            Route::prefix('attendance')->name('hr.attendance.')->group(function () {
                Route::get('/', AttendanceController::class.'@index')->name('index');
                Route::post('clock-in', AttendanceController::class.'@clockIn')->name('clock-in');
                Route::post('clock-out', AttendanceController::class.'@clockOut')->name('clock-out');
                Route::post('store', AttendanceController::class.'@store')->name('store');
                Route::get('report', AttendanceController::class.'@report')->name('report');

                Route::prefix('corrections')->name('corrections.')->group(function () {
                    Route::get('/', AttendanceCorrectionController::class.'@index')->name('index');
                    Route::get('create', AttendanceCorrectionController::class.'@create')->name('create');
                    Route::post('/', AttendanceCorrectionController::class.'@store')->name('store');
                    Route::get('{correction}', AttendanceCorrectionController::class.'@show')->name('show');
                    Route::post('{correction}/approve', AttendanceCorrectionController::class.'@approve')->name('approve');
                    Route::post('{correction}/reject', AttendanceCorrectionController::class.'@reject')->name('reject');
                });

                Route::get('{attendance}/edit', AttendanceController::class.'@edit')->name('edit');
                Route::put('{attendance}', AttendanceController::class.'@update')->name('update');
            });

            Route::prefix('leaves')->name('hr.leaves.')->group(function () {
                Route::get('/', LeaveController::class.'@index')->name('index');
                Route::get('create', LeaveController::class.'@create')->name('create');
                Route::post('/', LeaveController::class.'@store')->name('store');
                Route::get('balance', LeaveController::class.'@balance')->name('balance');
                Route::get('{leave}', LeaveController::class.'@show')->name('show');
                Route::post('{leave}/approve', LeaveController::class.'@approve')->name('approve');
                Route::post('{leave}/reject', LeaveController::class.'@reject')->name('reject');
            });

            Route::prefix('employees')->name('hr.employees.')->group(function () {
                Route::get('/', EmployeeController::class.'@index')->name('index');
                Route::get('create', EmployeeController::class.'@create')->name('create');
                Route::post('/', EmployeeController::class.'@store')->name('store');
                Route::get('{employee}', EmployeeController::class.'@show')->name('show');
                Route::get('{employee}/edit', EmployeeController::class.'@edit')->name('edit');
                Route::put('{employee}', EmployeeController::class.'@update')->name('update');
                Route::delete('{employee}', EmployeeController::class.'@destroy')->name('destroy');
            });

            Route::prefix('settlements')->name('hr.settlements.')->group(function () {
                Route::get('/', LeaveSettlementController::class.'@index')->name('index');
                Route::get('create', LeaveSettlementController::class.'@create')->name('create');
                Route::post('/', LeaveSettlementController::class.'@store')->name('store');
                Route::get('{settlement}/edit', LeaveSettlementController::class.'@edit')->name('edit');
                Route::put('{settlement}', LeaveSettlementController::class.'@update')->name('update');
                Route::delete('{settlement}', LeaveSettlementController::class.'@destroy')->name('destroy');
            });
        });
    });