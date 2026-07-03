<?php

use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\LookupController;
use App\Http\Controllers\Api\PersonnelController;
use App\Http\Controllers\Api\TimesheetController;
use App\Http\Controllers\Api\TripController;
use Illuminate\Support\Facades\Route;

Route::get('dashboard', [DashboardController::class, 'index']);

Route::get('departments', [LookupController::class, 'departments']);
Route::get('positions', [LookupController::class, 'positions']);

Route::apiResource('personnel', PersonnelController::class);
Route::apiResource('trips', TripController::class);
Route::apiResource('timesheets', TimesheetController::class);
