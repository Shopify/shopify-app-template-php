<?php

namespace App\Providers;

use App\Models\DbSessionStorage;
use Illuminate\Support\ServiceProvider;
use Shopify\Context;

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
            scopes: env('SCOPES'),
            hostName: env('HOST_NAME'),
            sessionStorage: new DbSessionStorage()
        );
    }
}
