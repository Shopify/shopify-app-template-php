<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Shopify\Auth\Session;
use Shopify\Context;
use Tests\TestCase;

class RootTest extends TestCase
{
    use RefreshDatabase;

    public function testRootRouteRedirectsToLoginIfShopIsNotInstalled()
    {
        $response = $this->get("?shop=test-shop.myshopify.io");
        $response->assertStatus(302);
        $response->assertRedirect("/login?shop=test-shop.myshopify.io");
    }

    public function testReturn200IfShopIsAlreadyInstalled()
    {
        $session = new Session(
            "test-session-id",
            "test-shop.myshopify.io",
            false,
            "test-session-state"
        );

        Context::$SESSION_STORAGE->storeSession($session);

        $response = $this->get("?shop=test-shop.myshopify.io");
        $response->assertStatus(200);
        $response->assertViewIs('react');
    }

    public function testUncaughtRequestsTriggerRouteBehaviour()
    {
        $session = new Session(
            "test-session-id",
            "test-shop.myshopify.io",
            false,
            "test-session-state"
        );

        Context::$SESSION_STORAGE->storeSession($session);

        $response = $this->get("/not-a-real-endpoint?shop=test-shop.myshopify.io");
        $response->assertStatus(200);
        $response->assertViewIs('react');
    }
}
