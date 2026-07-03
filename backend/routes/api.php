<?php

use App\Http\Controllers\Api\ActivityLogController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ExportController;
use App\Http\Controllers\Api\ImportController;
use App\Http\Controllers\Api\LookupController;
use App\Http\Controllers\Api\PersonnelController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\TimesheetController;
use App\Http\Controllers\Api\TripController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login'])->middleware('throttle:10,1');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);

    // Form select'leri birden fazla sayfada kullanılıyor; oturum yeterli.
    Route::get('departments', [LookupController::class, 'departments']);
    Route::get('positions', [LookupController::class, 'positions']);
    Route::get('lookup/personnel', [LookupController::class, 'personnel']);
    Route::get('lookup/trips', [LookupController::class, 'trips']);

    Route::get('dashboard', [DashboardController::class, 'index'])->middleware('page:dashboard');

    // Export route'ları resource route'lardan ÖNCE tanımlanmalı
    // (aksi halde "export" ifadesi {id} parametresi olarak eşleşir).
    Route::middleware('page:personel')->group(function () {
        Route::get('personnel/export/excel', [ExportController::class, 'personnelExcel']);
        Route::get('personnel/export/pdf', [ExportController::class, 'personnelPdf']);
        Route::get('personnel/import/template', [ImportController::class, 'personnelTemplate']);
        Route::post('personnel/import', [ImportController::class, 'personnelImport']);
        Route::apiResource('personnel', PersonnelController::class);
    });

    Route::middleware('page:seferler')->group(function () {
        Route::get('trips/export/excel', [ExportController::class, 'tripsExcel']);
        Route::get('trips/export/pdf', [ExportController::class, 'tripsPdf']);
        Route::apiResource('trips', TripController::class);
    });

    Route::middleware('page:puantajlar')->group(function () {
        Route::get('timesheets/export/excel', [ExportController::class, 'timesheetsExcel']);
        Route::get('timesheets/export/pdf', [ExportController::class, 'timesheetsPdf']);
        Route::apiResource('timesheets', TimesheetController::class);
    });

    // Rol, kullanıcı yönetimi ve işlem kayıtları yalnızca admin
    Route::middleware('admin')->group(function () {
        Route::apiResource('roles', RoleController::class)->except(['show']);
        Route::apiResource('users', UserController::class)->except(['show']);
        Route::get('activity-logs', [ActivityLogController::class, 'index']);
    });
});
