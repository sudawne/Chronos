<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Event; 
use Illuminate\Auth\Events\Login;

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
        Event::listen(Login::class, function ($event) {
            app(\App\Http\Controllers\MeetingController::class)->startApiServer();
            
        });

        // URL::forceRootUrl(config('app.url'));
        // if (str_contains(config('app.url'), 'https://')) {
        //     URL::forceRootUrl(config('app.url'));
        //     URL::forceScheme('https');
        // }
        if (str_contains(config('app.url'), 'https://')) {
            URL::forceScheme('https');
            URL::forceRootUrl(config('app.url'));
            
            request()->server->set('HTTPS', 'on');
            request()->headers->set('HOST', parse_url(config('app.url'), PHP_URL_HOST));
        }
    }
}
