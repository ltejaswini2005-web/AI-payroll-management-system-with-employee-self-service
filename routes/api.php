<?php

use App\Http\Middleware\EnsureValidAttendanceApiToken;
use App\Modules\Attendance\Http\Controllers\Api\AttendanceIngestionController;
use App\Modules\Attendance\Http\Controllers\Api\FaceAttendanceApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::post('/attendance/logs/bulk', [AttendanceIngestionController::class, 'bulkStore'])
        ->middleware([EnsureValidAttendanceApiToken::class, 'throttle:120,1']);

    // Face attendance API (no auth - shared kiosk)
    Route::prefix('face-attendance')->group(function (): void {
        Route::post('/recognize', [FaceAttendanceApiController::class, 'recognize'])->middleware('throttle:30,1');
        Route::post('/record', [FaceAttendanceApiController::class, 'recordAttendance'])->middleware('throttle:30,1');
    });
});
