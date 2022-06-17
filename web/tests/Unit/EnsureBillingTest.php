<?php

namespace Tests\Unit;

use App\Lib\EnsureBilling;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Shopify\Auth\Session;
use Shopify\Context;
use Tests\BaseTestCase;

class EnsureBillingTest extends BaseTestCase
{
    use RefreshDatabase;

    private Session $session;
    private const SHOPIFY_CHARGE_NAME = 'My Shopify App Billing';

    public function setUp(): void
    {
        parent::setUp();

        $this->session = new Session("1234", "test-domain.myshopify.io", true, "4321");
        $this->session->setScope(Context::$SCOPES->toString());
        $this->session->setAccessToken("access-token");
    }

    public function testRequiresSinglePaymentIfNoneExistsAndNonRecurring()
    {
        $this->mockGraphQLQueries(
            [
                "oneTimePurchases",
                [
                    "query" => "appPurchaseOneTimeCreate",
                    "variables" => ["name" => self::SHOPIFY_CHARGE_NAME],
                ],
            ],
            [self::EMPTY_SUBSCRIPTIONS, self::PURCHASE_ONE_TIME_RESPONSE]
        );

        list($hasPayment, $confirmationUrl) = EnsureBilling::check(
            $this->session,
            [
                "chargeName" => self::SHOPIFY_CHARGE_NAME,
                "amount" => 5,
                "interval" => EnsureBilling::INTERVAL_ONE_TIME,
                "currencyCode" => "USD",
            ]
        );

        $this->assertFalse($hasPayment);
        $this->assertEquals("https://totally-real-url", $confirmationUrl);
    }

    public function testRequiresSubscriptionIfNoneExistsAndRecurring()
    {
        $this->mockGraphQLQueries(
            [
                "activeSubscriptions",
                [
                    "query" => "appSubscriptionCreate",
                    "variables" => [
                        "name" => self::SHOPIFY_CHARGE_NAME,
                        "interval" => EnsureBilling::INTERVAL_EVERY_30_DAYS,
                    ],
                ],
            ],
            [self::EMPTY_SUBSCRIPTIONS, self::PURCHASE_SUBSCRIPTION_RESPONSE]
        );

        list($hasPayment, $confirmationUrl) = EnsureBilling::check(
            $this->session,
            [
                "chargeName" => self::SHOPIFY_CHARGE_NAME,
                "amount" => 5,
                "interval" => EnsureBilling::INTERVAL_EVERY_30_DAYS,
                "currencyCode" => "USD",
            ]
        );

        $this->assertFalse($hasPayment);
        $this->assertEquals("https://totally-real-url", $confirmationUrl);
    }

    public function testDoesNotRequireSinglePaymentIfExistsAndNonRecurring()
    {
        $this->mockGraphQLQueries(
            ["oneTimePurchases"],
            [self::EXISTING_ONE_TIME_PAYMENT]
        );

        list($hasPayment, $confirmationUrl) = EnsureBilling::check(
            $this->session,
            [
                "chargeName" => self::SHOPIFY_CHARGE_NAME,
                "amount" => 5,
                "interval" => EnsureBilling::INTERVAL_ONE_TIME,
                "currencyCode" => "USD",
            ]
        );

        $this->assertTrue($hasPayment);
        $this->assertNull($confirmationUrl);
    }

    public function testDoesNotRequireSubscriptionIfExistsAndRecurring()
    {
        $this->mockGraphQLQueries(
            ["activeSubscriptions"],
            [self::EXISTING_SUBSCRIPTION]
        );

        list($hasPayment, $confirmationUrl) = EnsureBilling::check(
            $this->session,
            [
                "chargeName" => self::SHOPIFY_CHARGE_NAME,
                "amount" => 5,
                "interval" => EnsureBilling::INTERVAL_EVERY_30_DAYS,
                "currencyCode" => "USD",
            ]
        );

        $this->assertTrue($hasPayment);
        $this->assertNull($confirmationUrl);
    }

    public function testIgnoresNonActiveOneTimePayments()
    {
        $this->mockGraphQLQueries(
            [
                "oneTimePurchases",
                [
                    "query" => "appPurchaseOneTimeCreate",
                    "variables" => ["name" => self::SHOPIFY_CHARGE_NAME],
                ],
            ],
            [self::EXISTING_INACTIVE_ONE_TIME_PAYMENT, self::PURCHASE_ONE_TIME_RESPONSE]
        );

        list($hasPayment, $confirmationUrl) = EnsureBilling::check(
            $this->session,
            [
                "chargeName" => self::SHOPIFY_CHARGE_NAME,
                "amount" => 5,
                "interval" => EnsureBilling::INTERVAL_ONE_TIME,
                "currencyCode" => "USD",
            ]
        );

        $this->assertFalse($hasPayment);
        $this->assertEquals("https://totally-real-url", $confirmationUrl);
    }

    public function testPaginatesUntilAPaymentIsFound()
    {
        $this->mockGraphQLQueries(
            [
                ["query" => "oneTimePurchases", "variables" => ["endCursor" => null]],
                ["query" => "oneTimePurchases", "variables" => ["endCursor" => "end_cursor"]],
            ],
            [self::EXISTING_ONE_TIME_PAYMENT_WITH_PAGINATION[0], self::EXISTING_ONE_TIME_PAYMENT_WITH_PAGINATION[1]]
        );

        list($hasPayment, $confirmationUrl) = EnsureBilling::check(
            $this->session,
            [
                "chargeName" => self::SHOPIFY_CHARGE_NAME,
                "amount" => 5,
                "interval" => EnsureBilling::INTERVAL_ONE_TIME,
                "currencyCode" => "USD",
            ]
        );

        $this->assertTrue($hasPayment);
        $this->assertNull($confirmationUrl);
    }

    private function mockGraphQLQueries(array $requests, array $responses)
    {
        $that = $this;

        $requestCallbacks = [];
        foreach ($requests as $expectedRequest) {
            $requestCallbacks[] = [$this->callback(function (Request $request) use ($that, $expectedRequest) {
                if (is_string($expectedRequest)) {
                    $that->assertStringContainsString($expectedRequest, $request->getBody()->__toString());
                } else {
                    $actualBody = json_decode($request->getBody()->__toString(), true);
                    $that->assertStringContainsString($expectedRequest["query"], $actualBody["query"]);
                    $that->assertArraySubset($expectedRequest["variables"], $actualBody["variables"]);
                }

                // If we got this far, the above asserts passed
                return true;
            })];
        }

        $responseObjects = [];
        foreach ($responses as $responseData) {
            $responseObjects[] = new Response(200, [], json_encode($responseData));
        }

        $client = $this->mockClient();
        $client->expects($this->exactly(count($requests)))
            ->method('sendRequest')
            ->withConsecutive(...$requestCallbacks)
            ->willReturnOnConsecutiveCalls(...$responseObjects);
    }

    private function assertArraySubset(array $expected, array $actual)
    {
        foreach ($expected as $key => $value) {
            $this->assertArrayHasKey($key, $expected);
            $this->assertEquals($expected[$key], $value);
        }
    }

    private const EMPTY_SUBSCRIPTIONS = [
        "data" => [
            "currentAppInstallation" => [
                "oneTimePurchases" => [
                    "edges" => [],
                    "pageInfo" => ["hasNextPage" => false, "endCursor" => null],
                ],
                "activeSubscriptions" => [],
                "userErrors" => [],
            ],
        ],
    ];

    private const EXISTING_ONE_TIME_PAYMENT = [
        "data" => [
            "currentAppInstallation" => [
                "oneTimePurchases" => [
                    "edges" => [
                        [
                            "node" => [
                                "name" => self::SHOPIFY_CHARGE_NAME,
                                "test" => true, "status" => "ACTIVE"
                            ],
                        ],
                    ],
                    "pageInfo" => ["hasNextPage" => false, "endCursor" => null],
                ],
                "activeSubscriptions" => [],
            ],
        ],
    ];

    private const EXISTING_ONE_TIME_PAYMENT_WITH_PAGINATION = [
        [
            "data" => [
                "currentAppInstallation" => [
                    "oneTimePurchases" => [
                        "edges" => [
                            [
                                "node" => ["name" => "some_other_name", "test" => true, "status" => "ACTIVE"],
                            ],
                        ],
                        "pageInfo" => ["hasNextPage" => true, "endCursor" => "end_cursor"],
                    ],
                    "activeSubscriptions" => [],
                ],
            ],
        ],
        [
            "data" => [
                "currentAppInstallation" => [
                    "oneTimePurchases" => [
                        "edges" => [
                            [
                                "node" => [
                                    "name" => self::SHOPIFY_CHARGE_NAME,
                                    "test" => true,
                                    "status" => "ACTIVE",
                                ],
                            ],
                        ],
                        "pageInfo" => ["hasNextPage" => false, "endCursor" => null],
                    ],
                    "activeSubscriptions" => [],
                ],
            ],
        ],
    ];

    private const EXISTING_INACTIVE_ONE_TIME_PAYMENT = [
        "data" => [
            "currentAppInstallation" => [
                "oneTimePurchases" => [
                    "edges" => [
                        [
                            "node" => [
                                "name" => self::SHOPIFY_CHARGE_NAME,
                                "test" => true,
                                "status" => "PENDING",
                            ],
                        ],
                    ],
                    "pageInfo" => ["hasNextPage" => false, "endCursor" => null],
                ],
                "activeSubscriptions" => [],
            ],
        ],
    ];

    private const EXISTING_SUBSCRIPTION = [
        "data" => [
            "currentAppInstallation" => [
                "oneTimePurchases" => [
                    "edges" => [],
                    "pageInfo" => ["hasNextPage" => false, "endCursor" => null],
                ],
                "activeSubscriptions" => [["name" => self::SHOPIFY_CHARGE_NAME, "test" => true]],
            ],
        ],
    ];

    private const PURCHASE_ONE_TIME_RESPONSE = [
        "data" => [
            "appPurchaseOneTimeCreate" => [
                "confirmationUrl" => "https://totally-real-url",
                "userErrors" => [],
            ],
        ],
    ];

    private const PURCHASE_SUBSCRIPTION_RESPONSE = [
        "data" => [
            "appSubscriptionCreate" => [
                "confirmationUrl" => "https://totally-real-url",
                "userErrors" => [],
            ],
        ],
    ];
}
