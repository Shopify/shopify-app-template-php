<?php

namespace App\Providers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     * @throws \Shopify\Exception\MissingArgumentException
     */
    public function boot()
    {
        // We force this because even though Ngrok will come over HTTPS, Laravel sees it as HTTP and will mix content.
        // If you have certificates configured with your local dev url, this is not necessary.
        if (App::environment('local')) {
            URL::forceScheme('https');
        }
        // Registry::addHandler(Topics::APP_UNINSTALLED, new AppUninstalled());
    }
}
