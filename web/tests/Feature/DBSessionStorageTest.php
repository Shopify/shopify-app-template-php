<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Shopify\Auth\AccessTokenOnlineUserInfo;
use Shopify\Auth\Session;
use Shopify\Context;
use Tests\TestCase;

class DBSessionStorageTest extends TestCase
{
    use RefreshDatabase;

    private const TEST_SESSION_ID = "test-session-id";
    /** @var Session */
    private $session;

    public function setUp(): void
    {
        parent::setUp();
        $this->session = new Session(self::TEST_SESSION_ID, "test-shop.myshopify.io", false, "test-session-state");
    }

    public function testLoadSessionReturnNullIfSessionDoesNotExist()
    {
        $this->assertNull(Context::$SESSION_STORAGE->loadSession(self::TEST_SESSION_ID));
    }

    public function testLoadSessionReturnSessionIfItExists()
    {
        $this->session->setScope('read_products,write_products');
        $this->session->setExpires(strtotime('+1 day'));
        $this->session->setAccessToken('totally_real_access_token');
        $this->session->setOnlineAccessInfo(
            new AccessTokenOnlineUserInfo(1, "firstname", "lastname", "email@host.com", true, true, "en-ca", false)
        );
        Context::$SESSION_STORAGE->storeSession($this->session);
        $this->assertEquals(
            $this->session,
            Context::$SESSION_STORAGE->loadSession(self::TEST_SESSION_ID)
        );
    }

    public function testLoadSessionReturnSessionIfItExistsWhenOptionalFieldsAreNotSet()
    {
        Context::$SESSION_STORAGE->storeSession($this->session);
        $this->assertEquals(
            $this->session,
            Context::$SESSION_STORAGE->loadSession(self::TEST_SESSION_ID)
        );
    }

    public function testLoadSessionReturnSessionWithoutOptionalFields()
    {
        Context::$SESSION_STORAGE->storeSession($this->session);
        $this->assertEquals(
            $this->session,
            Context::$SESSION_STORAGE->loadSession(self::TEST_SESSION_ID)
        );
    }

    public function testStoreShouldUpdateSession()
    {
        Context::$SESSION_STORAGE->storeSession($this->session);

        $this->assertTrue(Context::$SESSION_STORAGE->storeSession($this->session));
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
