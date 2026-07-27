<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Model;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Configurações de segurança do Eloquent
        Model::shouldBeStrict(!app()->isProduction());
        Model::preventLazyLoading(!app()->isProduction());
        Model::preventSilentlyDiscardingAttributes(!app()->isProduction());

        // Definir locale
        app()->setLocale(config('app.locale', 'pt'));

        // ─── Definir Rate Limiter para a API ─────────────────
        RateLimiter::for('api', function (Request $request) {
            // 60 pedidos por minuto por utilizador ou por IP
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}