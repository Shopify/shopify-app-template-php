<?php

namespace App\Providers;

use App\Lib\DbSessionStorage;
use App\Lib\Handlers\AppUninstalled;
use App\Lib\Handlers\Gdpr\CustomersDataRequest;
use App\Lib\Handlers\Gdpr\CustomersRedact;
use App\Lib\Handlers\Gdpr\ShopRedact;
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
        $host = str_replace('https://', '', env('HOST', 'not_defined'));

        $versionFilePath = dirname(__DIR__) . '/../version.txt';
        if (file_exists($versionFilePath)) {
            $templateVersion = trim(file_get_contents($versionFilePath));
        } else {
            $templateVersion = 'unknown';
        }

        Context::initialize(
            env('SHOPIFY_API_KEY', 'not_defined'),
            env('SHOPIFY_API_SECRET', 'not_defined'),
            env('SCOPES', 'not_defined'),
            $host,
            new DbSessionStorage(),
            // the following four params are needed in order to set userAgentPrefix
            '2022-04',                  // apiVersion
            true,                       // isEmbeddedApp, default = true
            false,                      // isPrivateApp, default = false
            null,                       // privateAppStorefrontAccessToken, default = null
            'PHP app template/' . $templateVersion  // userAgentPrefix
        );

        URL::forceRootUrl("https://$host");
        URL::forceScheme('https');

        Registry::addHandler(Topics::APP_UNINSTALLED, new AppUninstalled());

        /*
         * This sets up the mandatory GDPR webhooks. You’ll need to fill in the endpoint to be used by your app in the
         * “GDPR mandatory webhooks” section in the “App setup” tab, and customize the code when you store customer data
         * in the handlers being registered below.
         *
         * More details can be found on shopify.dev:
         * https://shopify.dev/apps/webhooks/configuration/mandatory-webhooks
         *
         * Note that you'll only receive these webhooks if your app has the relevant scopes as detailed in the docs.
         */
        Registry::addHandler('CUSTOMERS_DATA_REQUEST', new CustomersDataRequest());
        Registry::addHandler('CUSTOMERS_REDACT', new CustomersRedact());
        Registry::addHandler('SHOP_REDACT', new ShopRedact());
    }
}
