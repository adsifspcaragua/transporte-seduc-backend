<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // O link de redefinição aponta para o frontend (SPA), não para uma rota web do Laravel.
        ResetPassword::createUrlUsing(function ($notifiable, string $token) {
            $base = rtrim((string) env('FRONTEND_URL', config('app.url')), '/');

            return $base.'/reset-password?token='.$token.'&email='.urlencode($notifiable->getEmailForPasswordReset());
        });
    }
}
