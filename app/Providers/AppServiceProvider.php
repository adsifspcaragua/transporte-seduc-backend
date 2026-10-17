<?php

namespace App\Providers;

use App\Services\Presenca\Geocerca\PoliticaGeocerca;
use App\Services\Presenca\Geocerca\PontosFixos;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // A estrategia de geocerca ainda depende do PO (D01/D02). Regioes ou
        // corredor de rota entram aqui como novas implementacoes.
        $this->app->bind(PoliticaGeocerca::class, fn () => match (config('presenca.geocerca.politica')) {
            'pontos' => new PontosFixos,
            default => throw new InvalidArgumentException('Política de geocerca desconhecida: '.config('presenca.geocerca.politica')),
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Limites por conta, e nao por IP: varios estudantes podem sair pela
        // mesma rede movel. Uma tentativa de presenca sao 3 chamadas (QR,
        // desafio, confirmacao), entao 30/min deixa folga para novas leituras.
        RateLimiter::for('presenca', fn (Request $request) => Limit::perMinute(30)->by('presenca:'.($request->user()?->id ?: $request->ip())));
        RateLimiter::for('dispositivo', fn (Request $request) => Limit::perMinute(10)->by('dispositivo:'.($request->user()?->id ?: $request->ip())));

        // O link de redefinição aponta para o frontend (SPA), não para uma rota web do Laravel.
        ResetPassword::createUrlUsing(function ($notifiable, string $token) {
            $base = rtrim((string) env('FRONTEND_URL', config('app.url')), '/');

            return $base.'/reset-password?token='.$token.'&email='.urlencode($notifiable->getEmailForPasswordReset());
        });
    }
}
