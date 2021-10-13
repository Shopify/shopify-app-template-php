<?php

namespace App\Providers;

use App\Lib\DbSessionStorage;
use App\Lib\Handlers\AppUninstalled;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Shopify\Context;
use Shopify\Webhooks\Registry;
use Shopify\Webhooks\Topics;

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
        URL::forceScheme('https');
        // Registry::addHandler(Topics::APP_UNINSTALLED, new AppUninstalled());
    }
}
