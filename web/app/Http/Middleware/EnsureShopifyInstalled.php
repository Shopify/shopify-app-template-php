<?php

namespace App\Http\Middleware;

use App\Lib\AuthRedirection;
use App\Models\Session;
use Closure;
use Illuminate\Http\Request;
use Shopify\Context;
use Shopify\Utils;

class EnsureShopifyInstalled
{
    /**
     * Checks if the shop in the query arguments is currently installed.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $shop = $request->query('shop') ? Utils::sanitizeShopDomain($request->query('shop')) : null;

        $appInstalled = false;
        if ($shop) {
            $latestSession = Session::select('session_id')
                ->where('shop', $shop)
                ->where('access_token', '<>', null)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($latestSession) {
                $appInstalled = Context::$SESSION_STORAGE->loadSession($latestSession->session_id)->isValid();
            }
        }

        return $appInstalled ? $next($request) : AuthRedirection::redirect($request);
    }
}
