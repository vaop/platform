<?php

use App\Install\Controllers\AdminController;
use App\Install\Controllers\DatabaseController;
use App\Install\Controllers\EnvironmentController;
use App\Install\Controllers\FinalizeController;
use App\Install\Controllers\InstallController;
use App\Install\Middleware\EnsureInstallerEnabled;
use App\Install\Middleware\EnsureNotInstalled;
use Illuminate\Support\Facades\Route;

Route::middleware([EnsureInstallerEnabled::class, EnsureNotInstalled::class])
    ->prefix('install')
    ->name('install.')
    ->group(function () {
        Route::get('/', [InstallController::class, 'welcome'])->name('welcome');

        Route::get('/requirements', [InstallController::class, 'requirements'])->name('requirements');
        Route::post('/requirements', [InstallController::class, 'requirementsCheck'])->name('requirements.check');

        Route::get('/database', [DatabaseController::class, 'index'])->name('database');
        Route::post('/database', [DatabaseController::class, 'store'])->name('database.store');
        Route::post('/database/test', [DatabaseController::class, 'test'])->name('database.test');

        Route::get('/environment', [EnvironmentController::class, 'index'])->name('environment');
        Route::post('/environment', [EnvironmentController::class, 'store'])->name('environment.store');

        Route::get('/admin', [AdminController::class, 'index'])->name('admin');
        Route::post('/admin', [AdminController::class, 'store'])->name('admin.store');

        Route::get('/finalize', [FinalizeController::class, 'index'])->name('finalize');
        Route::post('/finalize', [FinalizeController::class, 'store'])->name('finalize.store');
    });

// Complete page is outside the middleware group because it's shown after
// installation when INSTALLER_ENABLED=false and storage/installed exists
Route::prefix('install')
    ->name('install.')
    ->group(function () {
        Route::get('/complete', [InstallController::class, 'complete'])->name('complete');
    });
