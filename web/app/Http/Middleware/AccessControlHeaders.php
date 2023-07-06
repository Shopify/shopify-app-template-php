<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Shopify\Context;

class AccessControlHeaders
{
    /**
     * Ensures that Access Control Headers are set for embedded apps.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (Context::$IS_EMBEDDED_APP) {

            /** @var Response $response */
            $response = $next($request);

            $response->headers->set("Access-Control-Allow-Origin", "*");
            $response->headers->set("Access-Control-Allow-Header", "Authorization");
            $response->headers->set("Access-Control-Expose-Headers", 'X-Shopify-API-Request-Failure-Reauthorize-Url');

            return $response;
        }
    }
}
