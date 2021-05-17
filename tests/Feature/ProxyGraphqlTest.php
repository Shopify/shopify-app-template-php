<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureShopifySession;
use Firebase\JWT\JWT;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Shopify\Auth\Session;
use Shopify\Context;

class ProxyGraphqlTest extends BaseTestCase
{
    use RefreshDatabase;

    public function testGraphqlProxyFetchesDataWithJWT()
    {
        $testGraphqlQuery = '{"variables":{},"query":"{\n shop {\n name\n __typename\n }\n}\n"}';

        $testGraphqlResponse = [
            "data" => [
                "shop" => [
                    "name" => "Shoppity Shop",
                ],
            ],
        ];

        $sessionId = 'test-shop.myshopify.com_42';
        Context::$IS_EMBEDDED_APP = true;
        $session = new Session(
            id: $sessionId,
            shop: 'test-shop.myshopify.io',
            isOnline: true,
            state: '1234',
        );
        $session->setScope('write_products');
        $session->setAccessToken('token');

        $this->assertTrue(Context::$SESSION_STORAGE->storeSession($session));
        $this->assertEquals($session, Context::$SESSION_STORAGE->loadSession('test-shop.myshopify.com_42'));

        $graphqlUrl = "https://test-shop.myshopify.io/admin/api/unstable/graphql.json";

        $client = $this->mockClient();
        $client->expects($this->exactly(2))
            ->method('sendRequest')
            ->withConsecutive(
                // The first request is made by the session authentication middleware, to make sure the token is active
                [$this->callback(function ($request) use ($graphqlUrl) {
                    // For some reason this callback is being run twice, so we need to make sure to rewind the body
                    // stream before grabbing the contents to test.
                    $request->getBody()->rewind();
                    return (
                        $request->getUri() == $graphqlUrl
                        && $request->getBody()->getContents() === EnsureShopifySession::TEST_GRAPHQL_QUERY
                    );
                })],
                [$this->callback(function ($request) use ($testGraphqlQuery, $graphqlUrl) {
                    $request->getBody()->rewind();
                    return (
                        $request->getUri() == $graphqlUrl
                        && $request->getBody()->getContents() === $testGraphqlQuery
                    );
                })],
            )
            ->willReturnOnConsecutiveCalls(
                new Response(status: 200, headers: [], body: '[]'),
                new Response(
                    status: 200,
                    headers: ["response-header" => "header-value"],
                    body: json_encode($testGraphqlResponse),
                ),
            );
        $token = $this->encodeJwtPayload();

        $response = $this->call(
            method: 'POST',
            uri: "/graphql",
            server: $this->transformHeadersToServerVars(['Authorization' => "Bearer $token"]),
            content: $testGraphqlQuery
        );

        $response->assertStatus(200);
        $response->assertExactJson($testGraphqlResponse);
        $response->assertHeader('response-header', 'header-value');
    }


    private function encodeJwtPayload(): string
    {
        $payload = [
            "iss" => "https://test-shop.myshopify.com/admin",
            "dest" => "https://test-shop.myshopify.com",
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
