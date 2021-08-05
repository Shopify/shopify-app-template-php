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
            env('SHOPIFY_API_KEY'),
            env('SHOPIFY_API_SECRET'),
            env('SCOPES'),
            str_replace('https://', '', env('HOST')),
            new DbSessionStorage()
        );

        URL::forceScheme('https');

        Registry::addHandler(Topics::APP_UNINSTALLED, new AppUninstalled());
    }
}
