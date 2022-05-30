<?php

namespace Tests\Feature;

use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Client\ClientInterface;
use Shopify\Clients\HttpClientFactory;
use Shopify\Context;
use Tests\TestCase;

class BaseTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Make sure that we don't make requests in tests by mistake
        $factory = $this->createMock(HttpClientFactory::class);
        $factory->expects($this->any())
            ->method('client');
        Context::$HTTP_CLIENT_FACTORY = $factory;
    }

    /**
     * @return ClientInterface|MockObject
     */
    protected function mockClient()
    {
        $client = $this->createMock(ClientInterface::class);
        $factory = $this->createMock(HttpClientFactory::class);
        $factory->expects($this->any())
            ->method('client')
            ->willReturn($client);
        Context::$HTTP_CLIENT_FACTORY = $factory;
        return $client;
    }
}
