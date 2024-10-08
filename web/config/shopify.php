<?php

use App\Lib\EnsureBilling;

return [

    /*
    |--------------------------------------------------------------------------
    | Shopify host
    |--------------------------------------------------------------------------
    |
    | The URL origin where the app will be accessed when it's deployed, excluding the protocol. This will be provided by your platform.
    | Example: my-deployed-app.fly.dev
    |
    | Learn more about in documentation: https://shopify.dev/docs/apps/launch/deployment/deploy-web-app/deploy-to-hosting-service#step-4-set-up-environment-variables
    |
    */
    'host' => env('HOST'),

    /*
    |--------------------------------------------------------------------------
    | Shopify custom domain
    |--------------------------------------------------------------------------
    |
    | One or more regexps to use when validating domains.
    |
    */
    'shop_custom_domain' => env('SHOP_CUSTOM_DOMAIN'),

    /*
    |--------------------------------------------------------------------------
    | Shopify API Key
    |--------------------------------------------------------------------------
    |
    | The client ID of the app, retrieved using Shopify CLI.
    |
    | Learn more about in documentation: https://shopify.dev/docs/apps/launch/deployment/deploy-web-app/deploy-to-hosting-service#step-4-set-up-environment-variables
    |
    */
    'api_key' => env('SHOPIFY_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Shopify API Secret
    |--------------------------------------------------------------------------
    |
    | The client secret of the app, retrieved using Shopify CLI.
    |
    | Learn more about in documentation: https://shopify.dev/docs/apps/launch/deployment/deploy-web-app/deploy-to-hosting-service#step-4-set-up-environment-variables
    |
    */
    'api_secret' => env('SHOPIFY_API_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Shopify Scopes
    |--------------------------------------------------------------------------
    |
    | The app's access scopes, retrieved using Shopify CLI. This is optional if you're using Shopify-managed installation.
    |
    | Learn more about in documentation: https://shopify.dev/docs/apps/launch/deployment/deploy-web-app/deploy-to-hosting-service#step-4-set-up-environment-variables
    |
    */
    'scopes' => env('SCOPES'),

    /*
    |--------------------------------------------------------------------------
    | Shopify billing
    |--------------------------------------------------------------------------
    |
    | You may want to charge merchants for using your app. Setting required to true will cause the EnsureShopifySession
    | middleware to also ensure that the session is for a merchant that has an active one-time payment or subscription.
    | If no payment is found, it starts off the process and sends the merchant to a confirmation URL so that they can
    | approve the purchase.
    |
    | Learn more about billing in our documentation: https://shopify.dev/docs/apps/billing
    |
    */
    'billing' => [
        'required' => false,

        // Example set of values to create a charge for $5 one time
        'chargeName' => 'My Shopify App One-Time Billing',
        'amount' => 5.0,
        'currencyCode' => 'USD', // Currently only supports USD
        'interval' => EnsureBilling::INTERVAL_ONE_TIME,
    ],

];
