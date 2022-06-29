<?php

namespace Tests\Unit;

use App\Lib\ProductCreator;
use Firebase\JWT\JWT;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Shopify\Auth\OAuth;
use Shopify\Auth\Session;
use Shopify\Context;
use Tests\BaseTestCase;

class ProductCreatorTest extends BaseTestCase
{
    use RefreshDatabase;

    private const SHOP = "test-shop.myshopify.com";
    private const USER_ID = "42";
    private const GRAPHQL_URL = "https://test-shop.myshopify.com/admin/api/2022-04/graphql.json";

    private string $sessionId;
    private Session $session;

    public function setUp(): void
    {
        parent::setUp();

        $this->sessionId = OAuth::getOfflineSessionId(self::SHOP);
        $this->session = new Session($this->sessionId, self::SHOP, false, "4321");
        $this->session->setScope(Context::$SCOPES->toString());
        $this->session->setAccessToken("access-token");

        Context::$SESSION_STORAGE->storeSession($this->session);
    }

    public function testHandlesCreatingProducts()
    {
        $token = $this->encodeJwtPayload();
        $this->mockGraphqlQueries();

        $response = $this
            ->withHeader("authorization", "Bearer ${token}")
            ->get("/api/products/create");

        $response->assertStatus(200);
        $response->assertJson(["success" => true, "error" => null]);
    }

    public function testHandlesProductCreationErrors()
    {
        $token = $this->encodeJwtPayload();
        $this->mockGraphqlQueries(true);

        $response = $this
            ->withHeader("authorization", "Bearer ${token}")
            ->get("/api/products/create");

        $response->assertStatus(400);
        $response->assertJson(["success" => false, "error" => "Something went wrong"]);
    }

    private function mockGraphqlQueries($failQuery = false)
    {
        // The first query is the shop check, to validate the access token
        $requestCallbacks = [
            []
        ];
        $responseObjects = [
            new Response(200, [], "{}"),
        ];

        if ($failQuery) {
            $requestCallbacks[] = [];
            $responseObjects[] = new Response(400, [], json_encode(["errors" => "Something went wrong"]));
        } else {
            for ($i = 0; $i < 5; $i++) {
                $requestCallbacks[] = [$this->callback(function (Request $request) {
                    return ($request->getUri() == self::GRAPHQL_URL &&
                        $request->getHeader("X-Shopify-Access-Token") === ["access-token"]
                    );
                })];
                $responseObjects[] = new Response(200, [], "{}");
            }
        }

        $client = $this->mockClient();
        $client->expects($this->exactly(count($requestCallbacks)))
            ->method('sendRequest')
            ->withConsecutive(...$requestCallbacks)
            ->willReturnOnConsecutiveCalls(...$responseObjects);
    }

    private function encodeJwtPayload(): string
    {
        $shop = self::SHOP;
        $payload = [
            "iss" => "https://$shop/admin",
            "dest" => "https://$shop",
            "aud" => "api-key-123",
            "sub" => self::USER_ID,
            "exp" => strtotime('+5 minutes'),
            "nbf" => 1591764998,
            "iat" => 1591764998,
            "jti" => "f8912129-1af6-4cad-9ca3-76b0f7621087",
            "sid" => "aaea182f2732d44c23057c0fea584021a4485b2bd25d3eb7fd349313ad24c685"
        ];
        return JWT::encode($payload, Context::$API_SECRET_KEY);
    }
}
