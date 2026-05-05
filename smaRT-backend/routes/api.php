<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BroadcastController;
use App\Http\Controllers\KasController;
use App\Http\Controllers\PanicController;
use App\Http\Controllers\SuratController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WargaController;
use App\Http\Controllers\LogController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — smaRT Backend
|--------------------------------------------------------------------------
|
| All routes are prefixed with /api automatically by Laravel.
| Auth endpoints are public; all others require a valid JWT token.
|
*/

// ─── Public (No Auth) ───────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

// ─── Protected (JWT Required) ───────────────────────────────────
Route::middleware('auth:api')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('role:PENGURUS,KETUA,BENDAHARA');

    // Warga Management
    Route::prefix('warga')->group(function () {
        Route::get('/', [WargaController::class, 'index'])
            ->middleware('role:PENGURUS,KETUA');
        Route::delete('/{id}', [WargaController::class, 'destroy'])
            ->middleware('role:PENGURUS,KETUA');
    });

    // Auth management
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register'])
            ->middleware('role:PENGURUS,KETUA');
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
    });

    // Surat Pengantar
    Route::prefix('surat')->group(function () {
        Route::get('/', [SuratController::class, 'index'])
            ->middleware('role:PENGURUS,KETUA');
        Route::post('/ajukan', [SuratController::class, 'store'])
            ->middleware('role:WARGA');
        Route::patch('/ajukan', [SuratController::class, 'review'])
            ->middleware('role:PENGURUS,KETUA');
    });

    // Panic Button / Laporan Darurat
    Route::prefix('panic')->group(function () {
        Route::get('/', [PanicController::class, 'index'])
            ->middleware('role:PENGURUS,KETUA');
        Route::post('/trigger', [PanicController::class, 'trigger'])
            ->middleware('role:WARGA');
    });

    // Kas / Blockchain
    Route::prefix('kas')->group(function () {
        Route::post('/input', [KasController::class, 'store'])
            ->middleware('role:BENDAHARA');
        Route::get('/history', [KasController::class, 'index']);
        Route::get('/monitor', [KasController::class, 'monitor']);
    });

    // Broadcast
    Route::get('/broadcast', [BroadcastController::class, 'index']);
    Route::post('/broadcast', [BroadcastController::class, 'store'])
        ->middleware('role:PENGURUS,KETUA');

    // System Activity Logs
    Route::get('/log', [LogController::class, 'index'])
        ->middleware('role:PENGURUS,KETUA');
});
