<?php

use Illuminate\Support\Facades\Route;

// --- Controladores de Auth ---
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Auth\RiderLoginController;

// --- Dashboards ---
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Rider\DashboardController as RiderDashboardController;

// --- Admin Resources ---
use App\Http\Controllers\Admin\RiderController as AdminRiderController;
use App\Http\Controllers\Admin\ForecastController;
use App\Http\Controllers\Admin\AccountController as AdminAccountController;
use App\Http\Controllers\Admin\AssignmentController as AdminAssignmentController;
use App\Http\Controllers\Admin\CoverageController;
use App\Http\Controllers\Admin\RiderStatusController;

// --- Admin Métricas ---
use App\Http\Controllers\Admin\MetricController;
use App\Http\Controllers\Admin\MetricSyncController;

// --- Rider ---
use App\Http\Controllers\Rider\ScheduleController as RiderScheduleController;
use App\Http\Controllers\Rider\ProfileController;

/*
|--------------------------------------------------------------------------
| Landing -> Login de Rider (público para riders no autenticados)
|--------------------------------------------------------------------------
*/
Route::get('/', [RiderLoginController::class, 'showLoginForm'])
    ->middleware('guest:rider')
    ->name('rider.login.form');

Route::post('/', [RiderLoginController::class, 'login'])
    ->middleware('guest:rider')
    ->name('rider.login');

/*
|--------------------------------------------------------------------------
| RUTAS DE ADMINISTRADOR (/admin/*)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {

    // --- Auth Admin ---
    Route::get('/login', [AdminLoginController::class, 'showLoginForm'])
        ->middleware('guest:web')
        ->name('login.form');

    Route::post('/login', [AdminLoginController::class, 'login'])
        ->middleware('guest:web')
        ->name('login');

    Route::post('/logout', [AdminLoginController::class, 'logout'])
        ->name('logout');

    // --- Panel protegido (auth:web) ---
    Route::middleware(['auth:web'])->group(function () {

        // Dashboard
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])
            ->name('dashboard');

        // CRUDs
        Route::resource('/riders', AdminRiderController::class);
        Route::resource('/forecasts', ForecastController::class)->only(['index', 'create', 'store']);
        Route::resource('/accounts', AdminAccountController::class);

        // Asignaciones
        Route::get('/accounts/{account}/assign', [AdminAssignmentController::class, 'create'])
            ->name('assignments.create');
        Route::post('/accounts/{account}/assign', [AdminAssignmentController::class, 'store'])
            ->name('assignments.store');
        Route::post('/assignments/{assignment}/end', [AdminAssignmentController::class, 'end'])
            ->name('assignments.end');

        // Cobertura
        Route::get('/coverage/{city?}/{week?}', [CoverageController::class, 'index'])
            ->name('coverage.index');

        // Estado de Riders
        Route::get('/rider-status/{city?}/{week?}', [RiderStatusController::class, 'index'])
            ->name('rider-status.index');

        // --- Métricas de Operación ---
        // Nota: el prefijo name('admin.') ya está aplicado al grupo, por lo que
        // estos nombres quedan como admin.metrics.index, admin.metrics.list, etc.
        Route::get('/metrics', [MetricController::class, 'index'])->name('metrics.index');
        Route::get('/metrics/list', [MetricController::class, 'list'])->name('metrics.list');
        Route::get('/metrics/kpis', [MetricController::class, 'kpis'])->name('metrics.kpis');
        Route::post('/metrics/sync', [MetricSyncController::class, 'sync'])->name('metrics.sync');
    });
});

/*
|--------------------------------------------------------------------------
| RUTAS DE RIDER (/rider/*)
|--------------------------------------------------------------------------
*/
Route::prefix('rider')->name('rider.')->group(function () {

    // --- Auth Rider ---
    Route::get('/login', [RiderLoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [RiderLoginController::class, 'login']);
    Route::post('/logout', [RiderLoginController::class, 'logout'])->name('logout');

    // --- Panel Rider (auth:rider) ---
    Route::middleware(['auth:rider'])->group(function () {

        // Dashboard y perfil
        Route::get('/dashboard', [RiderDashboardController::class, 'index'])->name('dashboard');
        Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');

        // Horario
        Route::get('/schedule/{week?}', [RiderScheduleController::class, 'index'])
            ->name('schedule.index');

        // Acciones AJAX horario
        Route::post('/schedule/select', [RiderScheduleController::class, 'selectSlot'])
            ->name('schedule.select');
        Route::post('/schedule/deselect', [RiderScheduleController::class, 'deselectSlot'])
            ->name('schedule.deselect');
    });
});
