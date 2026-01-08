<?php

namespace App\Providers;

use App\Services\Auth\AuthServices;
use App\Services\Email\EmailVerificationServices;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

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
        VerifyEmail::createUrlUsing(function ($notifiable) {
            $frontendUrl = 'http://localhost:5173/email/verify';

            $verifyUrl = URL::temporarySignedRoute(
                'auth.verification.verify',
                Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
                [
                    'id' => $notifiable->getKey(),
                    'hash' => sha1($notifiable->getEmailForVerification()),
                ]
            );

            $components = parse_url($verifyUrl);
            parse_str($components['query'] ?? '', $queryParams);

            return $frontendUrl.'/'.$notifiable->getKey().'/'.sha1($notifiable->getEmailForVerification()).
            '?expires='.($queryParams['expires'] ?? '').
            '&signature='.($queryParams['signature']) ?? '';
        });
    }
}
