<?php

namespace App\Providers;

use App\Services\Auth\AuthServices;
use App\Services\Email\EmailVerificationServices;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Email;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // $this->app->singleton(EmailVerificationServices::class, function ($app) {
        //     return new EmailVerificationServices();
        // });

        // $this->app->singleton(AuthServices::class, function ($app) {
        //     return new AuthServices($app->make(EmailVerificationServices::class));
        // });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
