<?php

namespace Tests\Feature;

use Firebase\JWT\JWT;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Shopify\Auth\OAuth;
use Shopify\Auth\Session;
use Shopify\Context;

class RestExampleTest extends BaseTestCase
{
    use RefreshDatabase;

    public function appTypeProvider()
    {
        return [
            'embedded app' => [true],
            'non-embedded app' => [false],
        ];
    }

    /**
     * @dataProvider appTypeProvider
     */
    public function testExampleRestRequest(bool $isEmbedded)
    {
        Context::$IS_EMBEDDED_APP = $isEmbedded;

        $sessionId = $isEmbedded ? 'test-shop.myshopify.io_42' : 'cookie-session-id';
        $session = new Session($sessionId, 'test-shop.myshopify.io', true, '1234');
        $session->setScope('write_products');
        $session->setAccessToken('token');

        $this->assertTrue(Context::$SESSION_STORAGE->storeSession($session));

        $graphqlUrl = "https://test-shop.myshopify.io/admin/api/unstable/graphql.json";
        $restUrl = "https://test-shop.myshopify.io/admin/api/unstable/products.json?limit=5";
        $restResponse = [
            'data' => [
                'products' => [
                    'name' => 'Test Product',
                    'amount' => 1,
                ],
            ]
        ];

        $client = $this->mockClient();
        $client->expects($this->exactly(2))
            ->method('sendRequest')
            ->withConsecutive(
                [$this->callback(
                    function ($request) use ($graphqlUrl) {
                        return $request->getUri() == $graphqlUrl;
                    }
                )],
                [$this->callback(
                    function ($request) use ($restUrl) {
                        return $request->getUri() == $restUrl;
                    }
                )],
            )
            ->willReturnOnConsecutiveCalls(
                new Response(200),
                new Response(200, [], json_encode($restResponse)),
            );

        $request = $this;
        $headers = [];
        if ($isEmbedded) {
            $token = $this->encodeJwtPayload();
            $headers['Authorization'] = "Bearer $token";
        } else {
            $signature = hash_hmac('sha256', $sessionId, Context::$API_SECRET_KEY);
            $request->withCredentials()
                ->withCookie(OAuth::SESSION_ID_COOKIE_NAME, $sessionId)
                ->withCookie(OAuth::SESSION_ID_SIG_COOKIE_NAME, $signature);
        }

        $response = $request->json('GET', "/rest-example", [], $headers);

        $response->assertStatus(200);
        $response->assertExactJson($restResponse);
    }

    private function encodeJwtPayload(): string
    {
        $payload = [
            "iss" => "https://test-shop.myshopify.io/admin",
            "dest" => "https://test-shop.myshopify.io",
            "aud" => "api-key-123",
            "sub" => "42",
            "exp" => strtotime('+5 minutes'),
            "nbf" => 1591764998,
            "iat" => 1591764998,
            "jti" => "f8912129-1af6-4cad-9ca3-76b0f7621087",
            "sid" => "aaea182f2732d44c23057c0fea584021a4485b2bd25d3eb7fd349313ad24c685"
        ];
        return JWT::encode($payload, Context::$API_SECRET_KEY);
    }
}
