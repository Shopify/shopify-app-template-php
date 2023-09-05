<?php

namespace App\Lib;

use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Shopify\Auth\Session;
use Shopify\Utils;

class ShopifyGuard implements Guard
{
    use GuardHelpers;

    /**
     * The Shopify session instance.
     *
     * @var \Shopify\Auth\Session
     */
    protected Session $session;

    /**
     * The user provider fetches stored Shopify sessions.
     *
     * @var \Illuminate\Contracts\Auth\UserProvider
     */
    protected UserProvider $provider;

    /**
     * Create a new authentication guard for Shopify sessions.
     *
     * @param  \Illuminate\Contracts\Auth\UserProvider  $provider
     */
    public function __construct(UserProvider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * Get the current Shopify session.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user()
    {
        if (! is_null($this->session)) {
            return $this->session;
        }

        $request = request();

        if (! $request->hasHeader('Authorization')) {
            return;
        }

        $shopifySession = Utils::loadCurrentSession(
            $request->header(),
            $request->cookie(),
            false
        );

        if (is_null($shopifySession)) {
            return;
        }

        $this->session = $this->provider->retrieveById($shopifySession->getId());

        return $this->session;
    }

    /**
     * Validate a user's credentials.
     *
     * Credentials are validated through Shopify sessions so we can bypass the
     * need to validate.
     *
     * @param  array  $credentials
     * @return bool
     */
    public function validate(array $credentials = [])
    {
        return true;
    }

    /**
     * Via remember is disabled for Shopify sessions.
     *
     * @return bool
     */
    public function viaRemember()
    {
        return false;
    }
}
