<?php

namespace App\Providers;

use App\Lib\DbSessionStorage;
use App\Lib\Handlers\AppUninstalled;
use Illuminate\Support\Facades\App;
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
        URL::forceScheme('https');

        // We can safely skip the next parts when running from the CLI (namely, from artisan) since they only matter in
        // HTTP requests or testing
        if (App::runningInConsole() && App::environment() !== "testing") {
            return;
        }

        Context::initialize(
            env('SHOPIFY_API_KEY', ''),
            env('SHOPIFY_API_SECRET', ''),
            env('SCOPES', ''),
            str_replace('https://', '', env('HOST', '')),
            new DbSessionStorage()
        );

        Registry::addHandler(Topics::APP_UNINSTALLED, new AppUninstalled());
    }
}
