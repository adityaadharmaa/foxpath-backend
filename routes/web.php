<?php

use App\Http\Controllers\EmailVerificationController;
use App\Services\Email\EmailVerificationServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/email/verify/{id}/{hash}', function (Request $request, $id, $hash) {
    $emailService = app(EmailVerificationServices::class);
    return $emailService->verify($request, $id, $hash);
})->middleware(['signed'])->name('verification.verify');