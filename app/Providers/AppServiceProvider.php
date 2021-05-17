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
        Context::initialize(
            apiKey: env('SHOPIFY_API_KEY'),
            apiSecretKey: env('SHOPIFY_API_SECRET_KEY'),
            scopes: env('SHOPIFY_SCOPES'),
            hostName: env('SHOPIFY_APP_HOST_NAME'),
            sessionStorage: new DbSessionStorage()
        );

        URL::forceScheme('https');

        Registry::addHandler(Topics::APP_UNINSTALLED, new AppUninstalled());
    }
}
