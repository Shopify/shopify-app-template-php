<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Shopify\Clients\Graphql;
use Shopify\Context;
use Shopify\Utils;

class EnsureShopifySession
{
    public const ACCESS_MODE_ONLINE = 'online';
    public const ACCESS_MODE_OFFLINE = 'offline';

    public const REDIRECT_HEADER = 'X-Shopify-API-Request-Failure-Reauthorize';
    public const REDIRECT_URL_HEADER = 'X-Shopify-API-Request-Failure-Reauthorize-Url';

    public const TEST_GRAPHQL_QUERY = <<<QUERY
    {
        shop {
            name
        }
    }
    QUERY;

    /**
     * Checks if there is currently an active Shopify session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $accessMode
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $accessMode = self::ACCESS_MODE_ONLINE)
    {
        switch ($accessMode) {
            case self::ACCESS_MODE_ONLINE:
                $isOnline = true;
                break;
            case self::ACCESS_MODE_OFFLINE:
                $isOnline = false;
                break;
            default:
                throw new Exception(
                    "Unrecognized access mode '$accessMode', accepted values are 'online' and 'offline'"
                );
        }

        $shop = Utils::sanitizeShopDomain($request->query('shop', ''));
        $session = Utils::loadCurrentSession($request->header(), $request->cookie(), $isOnline);

        if ($session && $shop && $session->getShop() !== $shop) {
            // This request is for a different shop. Go straight to login
            return redirect("/login?shop=$shop");
        }

        if ($session && $session->isValid()) {
            // If the session is valid, check if it's actually active by making a very simple request, and proceed
            $client = new Graphql($session->getShop(), $session->getAccessToken());
            $response = $client->query(self::TEST_GRAPHQL_QUERY);

            if ($response->getStatusCode() === 200) {
                $request->attributes->set('shopifySession', $session);
                return $next($request);
            }
        }

        if ($request->ajax()) {
            // If there is no shop in the URL, we may be able to grab the shop in the session or authentication header
            if (!$shop) {
                if ($session) {
                    $shop = $session->getShop();
                } elseif (Context::$IS_EMBEDDED_APP) {
                    $authHeader = $request->header('Authorization', '');
                    if (preg_match('/Bearer (.*)/', $authHeader, $matches) !== false) {
                        $payload = Utils::decodeSessionToken($matches[1]);
                        $shop = parse_url($payload['dest'], PHP_URL_HOST);
                    }
                }
            }

            return response('', 401, [
                self::REDIRECT_HEADER => '1',
                self::REDIRECT_URL_HEADER => "/login?shop=$shop",
            ]);
        } else {
            return redirect("/login?shop=$shop");
        }
    }
}
