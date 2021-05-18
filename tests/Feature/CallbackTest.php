<?php

namespace Tests\Feature;

use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Shopify\Auth\OAuth;
use Shopify\Auth\Session;
use Shopify\Context;

class CallbackTest extends BaseTestCase
{
    use RefreshDatabase;

    private string $domain = "test-shop.myshopify.io";

    private array $offlineResponse = [
        'access_token' => 'some access token',
        'scope' => 'read_products',
    ];
    private array $onlineResponse = [
        'access_token' => 'some access token',
        'scope' => 'read_products',
        'expires_in' => 525600,
        'associated_user_scope' => 'user_scope',
        'associated_user' => [
            'id' => 1,
            'first_name' => 'John',
            'last_name' => 'Smith',
            'email' => 'john@example.com',
            'email_verified' => true,
            'account_owner' => true,
            'locale' => 'en',
            'collaborator' => true,
        ],
    ];

    private array $webhookCheckEmpty = [
        'data' => [
            'webhookSubscriptions' => [
                'edges' => [],
            ],
        ],
    ];

    public function testCallBackForOfflineSession()
    {
        $offlineSession = new Session(
            "test-session-id",
            "test-shop.myshopify.io",
            false,
            "test-session-state"
        );

        // Session is already stored in the OAuth::begin
        Context::$SESSION_STORAGE->storeSession($offlineSession);

        $oauthTokenUrl = "https://test-shop.myshopify.io/admin/oauth/access_token";
        $graphqlUrl = "https://test-shop.myshopify.io/admin/api/unstable/graphql.json";

        $client = $this->mockClient();
        $client->expects($this->exactly(3))
            ->method('sendRequest')
            ->withConsecutive(
                [$this->callback(fn($request) => $request->getUri() == $oauthTokenUrl)],
                [$this->callback(fn($request) => $request->getUri() == $graphqlUrl)],
                [$this->callback(fn($request) => $request->getUri() == $graphqlUrl)],
            )
            ->willReturnOnConsecutiveCalls(
                new Response(status: 200, headers: [], body: json_encode($this->onlineResponse)),
                new Response(status: 200, headers: [], body: json_encode($this->webhookCheckEmpty)),
                new Response(status: 200, headers: [], body: '[]'),
            );

        $query = $this->requestQueryParameters();

        $response = $this
            ->withCookie(OAuth::SESSION_ID_COOKIE_NAME, $offlineSession->getId())
            ->get("/auth/callback?$query");

        $response->assertStatus(302);
        $response->assertRedirect(
            "?" . http_build_query(['host' => base64_encode($this->domain . "/admin"), 'shop' => $this->domain])
        );
    }

    private function requestQueryParameters(): string
    {
        $queryParameters = [
            'code' => '190a7aff728f86ec7cd29c695da6d341',
            'host' => base64_encode($this->domain . "/admin"),
            'shop' => 'test-shop.myshopify.io',
            'state' => 'test-session-state',
            'timestamp' => '1620186121',
        ];

        $computedHmac = hash_hmac('sha256', http_build_query($queryParameters), Context::$API_SECRET_KEY);

        $queryParameters['hmac'] = $computedHmac;

        return http_build_query($queryParameters);
    }

    public function testCallBackForOnlineSession()
    {
        $onlineSession = new Session(
            "test-session-id",
            "test-shop.myshopify.io",
            true,
            "test-session-state"
        );

        Context::$SESSION_STORAGE->storeSession($onlineSession);

        $oauthTokenUrl = "https://test-shop.myshopify.io/admin/oauth/access_token";
        $graphqlUrl = "https://test-shop.myshopify.io/admin/api/unstable/graphql.json";

        $client = $this->mockClient();
        $client->expects($this->exactly(3))
            ->method('sendRequest')
            ->withConsecutive(
                [$this->callback(fn($request) => $request->getUri() == $oauthTokenUrl)],
                [$this->callback(fn($request) => $request->getUri() == $graphqlUrl)],
                [$this->callback(fn($request) => $request->getUri() == $graphqlUrl)],
            )
            ->willReturnOnConsecutiveCalls(
                new Response(status: 200, headers: [], body: json_encode($this->onlineResponse)),
                new Response(status: 200, headers: [], body: json_encode($this->webhookCheckEmpty)),
                new Response(status: 200, headers: [], body: '[]'),
            );

        $query = $this->requestQueryParameters();

        $response = $this
            ->withCookie(OAuth::SESSION_ID_COOKIE_NAME, $onlineSession->getId())
            ->get("/auth/callback?$query");

        $response->assertRedirect(
            "?" . http_build_query(['host' => base64_encode($this->domain . "/admin"), 'shop' => $this->domain])
        );
    }
}
