<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Shopify\Context;
use Tests\TestCase;

class LoginRouteTest extends TestCase
{
    use RefreshDatabase;

    public function testLoginRouteSucceeds()
    {
        $queryParameters = [
            'client_id' => Context::$API_KEY,
            'scope' => Context::$SCOPES->toString(),
            'redirect_uri' => 'https://' . Context::$HOST_NAME . '/auth/callback',
            'grant_options' => ['per-user']
        ];

        $response = $this->withCookie('shopify_top_level_oauth', '1')->get("/login?shop=myshop");
        $response->assertStatus(302);

        $newLocation = $response->headers->get('Location');
        $queryString = parse_url($newLocation, PHP_URL_QUERY);
        parse_str($queryString, $actualParams);

        $this->assertMatchesRegularExpression(
            '/^https:\/\/myshop.myshopify.com\/admin\/oauth\/authorize.*/',
            $newLocation
        );

        unset($actualParams['state']);
        $this->assertEquals($queryParameters, $actualParams);
    }

    public function testLoginRouteRedirectsToTopLevel()
    {
        $response = $this->get("/login?shop=myshop");
        $response->assertStatus(302);

        $response->assertRedirect(
            'https://' . Context::$HOST_NAME . "/login/toplevel?shop=myshop.myshopify.com",
        );
    }
}
