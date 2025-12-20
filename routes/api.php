<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Criteria\CriteriaController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Program\ProgramController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\Users\UsersController;
use App\Services\Email\EmailVerificationServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->name('auth.')->group(function () {
        Route::post('/login', [AuthController::class, 'login'])->name('login');
        Route::post('/register', [AuthController::class, 'register'])->name('register');

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        });
    });

    Route::prefix('admin')->name('admin.')->middleware(['auth:sanctum', 'role:admin'])->group(function () {
        // Users Route Start
        Route::get('/users', [UsersController::class, 'index'])->name('users.index');
        Route::post('/users', [UsersController::class, 'store'])->name('users.store');
        Route::post('/users/{id}/reset-password', [UsersController::class, 'resetPassword'])->name('users.reset-password');
        Route::delete('/users/{id}', [UsersController::class, 'destroy'])->name('users.destroy');
        Route::patch('/users/{id}/restore', [UsersController::class, 'restore'])->name('users.restore');
        Route::get('/users/summary', [UsersController::class, 'summary'])->name('users.summary');
        Route::patch('/users/{id}/role', [UsersController::class, 'updateRole'])->name('users.update.role');
        Route::patch('/users/{id}/activate', [UsersController::class, 'activate'])->name('users.activate');
        Route::patch('/users/{id}/deactivate', [UsersController::class, 'deactivate'])->name('users.deactivate');
        Route::post('/users/{id}/resend-verification', [UsersController::class, 'resendVerification'])->name('users.resendverification');
        Route::post('/users/export', [UsersController::class, 'export'])->name('users.export');
        // Users Route End

        // Roles Route Start
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
        Route::get('/roles/{id}', [RoleController::class, 'show'])->name('roles.show');
        Route::patch('/roles/{id}', [RoleController::class, 'update'])->name('roles.update');
        Route::delete('/roles/{id}', [RoleController::class, 'destroy'])->name('roles.destroy');
        // Roles Route End

        // Programs Routes Start
        Route::apiResource('programs', ProgramController::class)->except('show');
        Route::get('programs/summary', [ProgramController::class, 'summary'])->name('programs.summary');
        // Route::get('programs/{id}/stats', [ProgramController::class, 'stats'])->name('programs.stats');
        Route::post('programs/export', [ProgramController::class, 'export'])->name('programs.export');
        Route::patch('programs/{id}/toggle', [ProgramController::class, 'activate'])->name('programs.activate');
        Route::patch('programs/{id}/restore', [ProgramController::class, 'restore'])->name('programs.restore');
        // Programs Routes End

        // Criteria Routes Start
        Route::apiResource('criteria', CriteriaController::class);
        Route::patch('criteria/{id}/restore', [CriteriaController::class, 'restore'])->name('criteria.restore');
        Route::patch('criteria/{id}/toggle', [CriteriaController::class, 'toggle'])->name('criteria.activate');
        Route::post('criteria/export', [CriteriaController::class, 'export'])->name('criteria.export');
        // Criteria Routes End
    });

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::put('/profile', [ProfileController::class, 'updateProfile'])->name('profile.update');
    });

    Route::prefix('user')->name('user.')->middleware(['auth:sanctum'])->group(function () {
        Route::get('/profile', [ProfileController::class, 'getProfile'])->name('profile.get');
        Route::get('programs', [ProgramController::class, 'index'])->name('user.programs-index');
    });

    Route::post('/email/resend', function (Request $request) {
        $emailService = app(EmailVerificationServices::class);
        return $emailService->resend($request);
    })->middleware(['auth:sanctum', 'throttle:6,1'])->name('verification.send');
});
