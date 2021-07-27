<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\MockObject\MockObject;
use Shopify\Clients\HttpHeaders;
use Shopify\Context;
use Shopify\Webhooks\Handler;
use Shopify\Webhooks\Registry;
use Shopify\Webhooks\Topics;

class WebhookTest extends BaseTestCase
{
    use RefreshDatabase;

    /** @var string */
    private $domain = "test-shop.myshopify.io";

    public function testWebhookIsProcessed()
    {
        $shop = $this->domain;
        $topic = Topics::APP_UNINSTALLED;
        $body = ['dummy' => 'data'];

        /** @var MockObject|Handler */
        $mock = $this->getMockBuilder(Handler::class)
            ->getMock();
        $mock->expects($this->once())
            ->method('handle')
            ->with($topic, $shop, $body);

        Registry::addHandler($topic, $mock);

        $hmac = base64_encode(hash_hmac('sha256', json_encode($body), Context::$API_SECRET_KEY, true));
        $response = $this->json(
            'POST',
            "/webhooks",
            $body,
            [
                HttpHeaders::X_SHOPIFY_TOPIC => $topic,
                HttpHeaders::X_SHOPIFY_DOMAIN => $shop,
                HttpHeaders::X_SHOPIFY_HMAC => $hmac,
            ],
        );

        $response->assertStatus(200);
    }
}
