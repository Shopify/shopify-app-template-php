<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Shopify\Auth\Session;
use Shopify\Context;
use Tests\TestCase;


class SessionStorageTest extends TestCase
{
    use RefreshDatabase;

    const TEST_SESSION_ID = "test-session-id";
    private Session $session;

    public function setUp(): void
    {
        parent::setUp();
        $this->session = new Session(
            self::TEST_SESSION_ID,
            "test-shop.myshopify.io",
            true,
            "test-session-state"
        );
    }

    public function testLoadSessionReturnNullIfSessionDoesNotExist()
    {
        $this->assertNull(Context::$SESSION_STORAGE->loadSession(self::TEST_SESSION_ID));
    }

    public function testLoadSessionReturnSessionIfItExists()
    {
        Context::$SESSION_STORAGE->storeSession($this->session);
        $this->assertEquals(
            $this->session,
            Context::$SESSION_STORAGE->loadSession(self::TEST_SESSION_ID)
        );
    }

    public function testStoreSessionReturnFalseIfOperationFails()
    {
        Context::$SESSION_STORAGE->storeSession($this->session);

        // The second store will fail because session_id should be unique
        $this->assertFalse(Context::$SESSION_STORAGE->storeSession($this->session));
    }

    public function testStoreSessionReturnTrueIfOperationSucceeds()
    {
        $this->assertTrue(Context::$SESSION_STORAGE->storeSession($this->session));
    }

    public function testDeleteSessionReturnFalseIfOperationFails()
    {
        $this->assertFalse(Context::$SESSION_STORAGE->deleteSession(self::TEST_SESSION_ID));
    }

    public function testDeleteSessionReturnTrueIfOperationSucceeds()
    {
        Context::$SESSION_STORAGE->storeSession($this->session);
        $this->assertTrue(Context::$SESSION_STORAGE->deleteSession(self::TEST_SESSION_ID));
    }

}
