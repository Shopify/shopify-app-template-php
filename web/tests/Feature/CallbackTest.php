<?php

namespace Tests\Feature;

use App\Lib\EnsureBilling;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Shopify\Auth\OAuth;
use Shopify\Auth\Session;
use Shopify\Context;
use Tests\BaseTestCase;

class CallbackTest extends BaseTestCase
{
    use RefreshDatabase;

    /** @var string */
    private $domain = "test-shop.myshopify.io";

    /** @var array */
    private $offlineResponse = [
        'access_token' => 'some access token',
        'scope' => 'read_products',
    ];

    /** @var array */
    private $onlineResponse = [
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

    /** @var array */
    private $webhookCheckEmpty = [
        'data' => [
            'webhookSubscriptions' => [
                'edges' => [],
            ],
        ],
    ];

    /** @var array */
    private $emptySubscriptions = [
        'data' => [
            'currentAppInstallation' => [
                'oneTimePurchases' => [
                    'edges' => [],
                    'pageInfo' => ['hasNextPage' => false, 'endCursor' => null],
                ],
                'activeSubscriptions' => [],
                'userErrors' => [],
            ],
        ],
    ];

    /** @var array */
    private $purchaseOneTimeResponse = [
        'data' => [
            'appPurchaseOneTimeCreate' => [
                'confirmationUrl' => 'https://totally-real-url',
                'userErrors' => [],
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

        $this->mockCallbackRequests();

        $query = $this->requestQueryParameters();

        $signature = hash_hmac('sha256', $offlineSession->getId(), Context::$API_SECRET_KEY);
        $response = $this
            ->withCookie(OAuth::SESSION_ID_COOKIE_NAME, $offlineSession->getId())
            ->withCookie(OAuth::SESSION_ID_SIG_COOKIE_NAME, $signature)
            ->get("/api/auth/callback?$query");

        $response->assertStatus(302);
        $response->assertRedirect(
            "?" . http_build_query(['host' => base64_encode($this->domain . "/admin"), 'shop' => $this->domain])
        );
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

        $this->mockCallbackRequests();

        $query = $this->requestQueryParameters();

        $signature = hash_hmac('sha256', $onlineSession->getId(), Context::$API_SECRET_KEY);
        $response = $this
            ->withCookie(OAuth::SESSION_ID_COOKIE_NAME, $onlineSession->getId())
            ->withCookie(OAuth::SESSION_ID_SIG_COOKIE_NAME, $signature)
            ->get("/api/auth/callback?$query");

        $response->assertRedirect(
            "?" . http_build_query(['host' => base64_encode($this->domain . "/admin"), 'shop' => $this->domain])
        );
    }

    public function testRedirectsToBillingWhenNoPaymentIsPresent()
    {
        Config::set("shopify.billing", [
            "chargeName" => "My Shopify App One-Time Billing",
            "required" => true,
            "amount" => 1,
            "currencyCode" => "USD",
            "interval" => EnsureBilling::INTERVAL_ONE_TIME,
        ]);

        $session = new Session(
            "test-session-id",
            "test-shop.myshopify.io",
            true,
            "test-session-state"
        );

        // Session is already stored in the OAuth::begin
        Context::$SESSION_STORAGE->storeSession($session);

        $this->mockCallbackRequests(true);

        $query = $this->requestQueryParameters();

        $signature = hash_hmac('sha256', $session->getId(), Context::$API_SECRET_KEY);
        $response = $this
            ->withCookie(OAuth::SESSION_ID_COOKIE_NAME, $session->getId())
            ->withCookie(OAuth::SESSION_ID_SIG_COOKIE_NAME, $signature)
            ->get("/api/auth/callback?$query");

        $response->assertStatus(302);
        $response->assertRedirect("https://totally-real-url");
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

    /**
     * @return ClientInterface|MockObject
     */
    private function mockCallbackRequests($addBillingCalls = false)
    {
        $oauthTokenUrl = "https://test-shop.myshopify.io/admin/oauth/access_token";
        $graphqlUrl = "https://test-shop.myshopify.io/admin/api/2022-04/graphql.json";

        $expectedCalls = [
            [$this->callback(function ($request) use ($oauthTokenUrl) {
                return $request->getUri() == $oauthTokenUrl;
            })],
            [$this->callback(function ($request) use ($graphqlUrl) {
                return $request->getUri() == $graphqlUrl;
            })],
            [$this->callback(function ($request) use ($graphqlUrl) {
                return $request->getUri() == $graphqlUrl;
            })]
        ];
        $expectedResponses = [
            new Response(200, [], json_encode($this->onlineResponse)),
            new Response(200, [], json_encode($this->webhookCheckEmpty)),
            new Response(200, [], '[]'),
        ];

        if ($addBillingCalls) {
            $that = $this;
            $expectedCalls[] = [$this->callback(function (Request $request) use ($that) {
                $that->assertStringContainsString("oneTimePurchases", $request->getBody()->__toString());
                return true;
            })];
            $expectedCalls[] = [$this->callback(function (Request $request) use ($that) {
                $that->assertStringContainsString("appPurchaseOneTimeCreate", $request->getBody()->__toString());
                return true;
            })];

            $expectedResponses[] = new Response(200, [], json_encode($this->emptySubscriptions));
            $expectedResponses[] = new Response(200, [], json_encode($this->purchaseOneTimeResponse));
        }

        $client = $this->mockClient();
        $client->expects($this->exactly(count($expectedCalls)))
            ->method('sendRequest')
            ->withConsecutive(...$expectedCalls)
            ->willReturnOnConsecutiveCalls(...$expectedResponses);

        return $client;
    }
}
