<?php

use App\Http\Controllers\Applicants\ApplicationDecisionController;
use App\Http\Controllers\Applicants\ApplicationPlacementController;
use App\Http\Controllers\Applicants\ApplicationScoreController;
use App\Http\Controllers\ApplicationDocument\ApplicationDocumentController;
use App\Http\Controllers\ApplicationDocument\ApplicationDocumentReviewController;
use App\Http\Controllers\ApplicationDocument\ApplicationDocumentStatusController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Criteria\CriteriaController;
use App\Http\Controllers\InternshipApplication\InternshipApplicationController;
use App\Http\Controllers\Notification\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProfileEducation\ProfileEducationController;
use App\Http\Controllers\Program\ProgramController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SAW\SAWController;
use App\Http\Controllers\Users\UsersController;
use App\Http\Controllers\VerificationController;
use App\Services\Email\EmailVerificationServices;
use App\Services\InternshipApplication\InternshipApplicationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->name('auth.')->group(function () {
        Route::post('/login', [AuthController::class, 'login'])->name('login');
        Route::post('/register', [AuthController::class, 'register'])->name('register');
        Route::post('/forgot-password', ForgotPasswordController::class);
        Route::post('/reset-password', ResetPasswordController::class);
        Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
        ->middleware(['signed','throttle:6,1'])
        ->name('verification.verify');
        Route::post('/email/resend-public', [VerificationController::class, 'resendPublic'])
        ->middleware('throttle:6,1')
        ->name('verification.resend.public');

        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/me', [AuthController::class, 'me'])->name('me');
            Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
            Route::post('/email/resend', [VerificationController::class, 'resend'])->name('verification.send');
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
        Route::get('/roles/summary', [RoleController::class, 'summary'])->name('roles.summary');
        Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
        Route::get('/roles/{id}', [RoleController::class, 'show'])->name('roles.show');
        Route::patch('/roles/{id}', [RoleController::class, 'update'])->name('roles.update');
        Route::delete('/roles/{id}', [RoleController::class, 'destroy'])->name('roles.destroy');
        // Roles Route End

        // Programs Routes Start
        Route::get('programs/summary', [ProgramController::class, 'summary'])->name('programs.summary');
        Route::apiResource('programs', ProgramController::class);
        Route::get('programs/{program}/applicants', [ProgramController::class, 'applicants'])->name('programs.applicants.applicants');
        // Route::get('programs/{id}/stats', [ProgramController::class, 'stats'])->name('programs.stats');
        Route::post('programs/export', [ProgramController::class, 'export'])->name('programs.export');
        Route::post('programs/{id}/decide', [ApplicationDecisionController::class, 'decide'])->name('programs.decided');
        Route::patch('programs/{id}/toggle', [ProgramController::class, 'activate'])->name('programs.activate');
        Route::patch('programs/{id}/restore', [ProgramController::class, 'restore'])->name('programs.restore');
        Route::post('programs/{id}/calculate-saw', [SAWController::class, 'calculate'])->name('programs.saw-calculate');
         Route::get('programs/{id}/saw-details', [SAWController::class, 'details'])->name('programs.saw-details');
        // Programs Routes End

        // Criteria Routes Start
        Route::apiResource('criteria', CriteriaController::class);
        Route::patch('criteria/{id}/restore', [CriteriaController::class, 'restore'])->name('criteria.restore');
        Route::patch('criteria/{id}/toggle', [CriteriaController::class, 'toggle'])->name('criteria.activate');
        Route::post('criteria/export', [CriteriaController::class, 'export'])->name('criteria.export');
        // Criteria Routes End

        // Routes Application Start
        Route::get('/applications', [InternshipApplicationController::class, 'index'])->name('applicants.application.index');
        Route::get('/applications/{id}', [InternshipApplicationController::class, 'show'])->name('applicants.application.show');
        Route::post('/applications/{id}/score', [ApplicationScoreController::class, 'store'])->name('applicants.application.score.store');
        Route::patch('/applications/{id}/status', [InternshipApplicationController::class, 'updateStatus'])->name('applicants.application.status.update');
        Route::patch('/applications/{id}/placement', [ApplicationPlacementController::class, 'update'])->name('applications.placement');
        // Routes Application End

        Route::patch('/documents/{id}/review', [ApplicationDocumentReviewController::class, 'review'])->name('document.review');
    });

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::put('/profile', [ProfileController::class, 'updateProfile'])->name('profile.update');

        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/', [NotificationController::class, 'index'])->name('index');
            Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('unread-count');
            Route::post('/mark-read', [NotificationController::class, 'markAsRead'])->name('mark-read');
            Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
        });
    });

    Route::prefix('user')->name('user.')->middleware(['auth:sanctum'])->group(function () {
        Route::get('/profile-full', [ProfileController::class, 'showFull'])->name('profile.show.all');
        Route::get('/profile', [ProfileController::class, 'getProfile'])->name('profile.get');
        Route::put('/profile', [ProfileController::class, 'updateProfile'])->name('profile.update');

        Route::get('/profile/educations', [ProfileEducationController::class, 'show'])->name('profiles.education.get');
        Route::post('/profile/educations', [ProfileEducationController::class, 'store'])->name('profiles.education.store');
        // Route::patch('/profile/educations/{education}', [ProfileEducationController::class, 'update'])->name('profiles.education.update');
        // Route::delete('/profile/educations/{education}', [ProfileEducationController::class, 'destroy'])->name('profiles.education.delete');
        Route::get('programs', [ProgramController::class, 'index'])->name('user.programs-index');
        Route::get('/applications', [InternshipApplicationController::class, 'index'])->name('applications.info');
        Route::get('/applications/{id}', [InternshipApplicationController::class, 'show'])->name('applications.info');
        Route::post('/applications', [InternshipApplicationController::class, 'store'])->name('applications.store');
        Route::post('/applications/{application}/documents', [ApplicationDocumentController::class, 'store'])->name('applications.documents.upload');
        Route::get('/applications/{application}/document-status', [ApplicationDocumentStatusController::class, 'show'])->name('applications.documents.status');
    });

    Route::post('/email/resend', function (Request $request) {
        $emailService = app(EmailVerificationServices::class);
        return $emailService->resend($request);
    })->middleware(['auth:sanctum', 'throttle:6,1'])->name('verification.send');
});
