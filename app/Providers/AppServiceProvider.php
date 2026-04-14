<?php

namespace App\Providers;

use App\Services\Auth\AuthServices;
use App\Services\Email\EmailVerificationServices;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\RateLimiter;
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
            $frontendUrl = config('app.frontend_url') . '/email/verify';

            $verifyUrl = URL::temporarySignedRoute(
                'auth.verification.verify',
                Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
                [
                    'id' => $notifiable->getKey(),
                    'hash' => sha1($notifiable->getEmailForVerification()),
                ]
            );

            $queryParams = parse_url($verifyUrl, PHP_URL_QUERY);

            return $frontendUrl . '/' . $notifiable->getKey() . '/' . sha1($notifiable->getEmailForVerification()) . '?' . $queryParams;
        });

        // RateLimiter::for('email-outbound', function (object $job) {
        //     return Limit::perMinute(20)->by('email-outbound');
        // });
    }
}
