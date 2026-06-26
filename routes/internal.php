<?php

use App\Http\Controllers\Internal\HealthCheckController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth.basic')->group(function (): void {
    Route::get('/internal/health', [HealthCheckController::class, 'index'])->name('internal.health');
});
