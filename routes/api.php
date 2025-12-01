<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Services\Email\EmailVerificationServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');
Route::prefix('v1')->group(function(){
    Route::prefix('auth')->name('auth.')->group(function () {
        Route::post('/login', [AuthController::class, 'login'])->name('login');
        Route::post('/register', [AuthController::class, 'register'])->name('register');

        Route::middleware('auth:sanctum')->group(function(){
            Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        });
    });

    Route::prefix('admin')->name('admin.')->middleware(['auth:sanctum', 'role:admin'])->group(function(){
            // Roles Route Start
            Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
            Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
            Route::get('/roles/{id}', [RoleController::class, 'show'])->name('roles.show');
            Route::patch('/roles/{id}', [RoleController::class, 'update'])->name('roles.update');
            Route::delete('/roles/{id}', [RoleController::class, 'destroy'])->name('roles.destroy');
    });

    Route::middleware(['auth:sanctum'])->group(function(){
        Route::put('/profile', [ProfileController::class, 'updateProfile'])->name('profile.update');
    });

    Route::post('/email/resend', function (Request $request) {
        $emailService = app(EmailVerificationServices::class);
        return $emailService->resend($request);
    })->middleware(['auth:sanctum', 'throttle:6,1'])->name('verification.send');
});