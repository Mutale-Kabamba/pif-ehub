<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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
        $host = request()->getHost();
        $isLocal = in_array($host, ['127.0.0.1', 'localhost', '::1']) || str_ends_with($host, '.test');

        if (! $isLocal && (config('app.env') === 'production' || request()->header('X-Forwarded-Proto') === 'https' || str_contains($host, 'laravel.cloud'))) {
            URL::forceScheme('https');
        }
    }
}
