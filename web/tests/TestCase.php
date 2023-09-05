<?php

namespace Tests;

use App\Http\Middleware\EnsureShopifyInstalled;
use App\Http\Middleware\EnsureShopifySession;
use Firebase\JWT\JWT;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Config;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Override the actingAs method to inject Shopify specific testing capabilities.
     *
     * Disables Shopify middleware and generates a mock Bearer token to successfully
     * authenticate against Shopify for testing purposes.
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable $session
     * @param string|null $guard
     *
     * @return self
     */
    public function actingAs(Authenticatable $session, $guard = null): self
    {
        $this->withoutMiddleware([
            EnsureShopifyInstalled::class,
            EnsureShopifySession::class
        ]);

        $shopifySecret = Config::get('services.shopify.secret');

        $token = JWT::encode(['dest' => $session->shop], $shopifySecret, 'HS256');

        return parent::actingAs($session, $guard)->withToken($token);
    }
}
