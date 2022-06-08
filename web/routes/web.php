<?php

use App\Lib\EnsureBilling;
use App\Models\Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Shopify\Auth\OAuth;
use Shopify\Auth\Session as AuthSession;
use Shopify\Clients\HttpHeaders;
use Shopify\Clients\Rest;
use Shopify\Context;
use Shopify\Utils;
use Shopify\Webhooks\Registry;
use Shopify\Webhooks\Topics;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::fallback(function (Request $request) {
    $shop = $request->query('shop') ? Utils::sanitizeShopDomain($request->query('shop')) : null;
    $appInstalled = Session::where('shop', $shop)->exists();
    if ($appInstalled) {
        if (env('APP_ENV') === 'production') {
            return file_get_contents(public_path('index.html'));
        } else {
            return file_get_contents(base_path('frontend/index.html'));
        }
    }
    return redirect("/api/auth?shop=$shop");
});

Route::get('/api/auth/toplevel', function (Request $request, Response $response) {
    $shop = Utils::sanitizeShopDomain($request->query('shop'));

    $response = new Response(view('top_level', [
        'apiKey' => Context::$API_KEY,
        'shop' => $shop,
        'hostName' => Context::$HOST_NAME,
    ]));

    $response->withCookie(cookie()->forever('shopify_top_level_oauth', '', null, null, true, true, false, 'strict'));

    return $response;
});

Route::get('/api/auth', function (Request $request) {
    $shop = Utils::sanitizeShopDomain($request->query('shop'));

    if (!$request->hasCookie('shopify_top_level_oauth')) {
        return redirect("/api/auth/toplevel?shop=$shop");
    }

    $installUrl = OAuth::begin(
        $shop,
        '/api/auth/callback',
        true,
        ['App\Lib\CookieHandler', 'saveShopifyCookie'],
    );

    return redirect($installUrl);
});

Route::get('/api/auth/callback', function (Request $request) {
    $session = OAuth::callback(
        $request->cookie(),
        $request->query(),
        ['App\Lib\CookieHandler', 'saveShopifyCookie'],
    );

    $host = $request->query('host');
    $shop = Utils::sanitizeShopDomain($request->query('shop'));

    $response = Registry::register('/api/webhooks', Topics::APP_UNINSTALLED, $shop, $session->getAccessToken());
    if ($response->isSuccess()) {
        Log::debug("Registered APP_UNINSTALLED webhook for shop $shop");
    } else {
        Log::error(
            "Failed to register APP_UNINSTALLED webhook for shop $shop with response body: " .
                print_r($response->getBody(), true)
        );
    }

    $redirectUrl = "?" . http_build_query(['host' => $host, 'shop' => $shop]);
    if (Config::get('shopify.billing.required')) {
        list($hasPayment, $confirmationUrl) = EnsureBilling::check($session, Config::get('shopify.billing'));

        if (!$hasPayment) {
            $redirectUrl = $confirmationUrl;
        }
    }

    return redirect($redirectUrl);
});

Route::post('/api/graphql', function (Request $request) {
    $response = Utils::graphqlProxy($request->header(), $request->cookie(), $request->getContent());

    $xHeaders = array_filter(
        $response->getHeaders(),
        function ($key) {
            return str_starts_with($key, 'X') || str_starts_with($key, 'x');
        },
        ARRAY_FILTER_USE_KEY
    );

    return response($response->getDecodedBody(), $response->getStatusCode())->withHeaders($xHeaders);
})->middleware('shopify.auth:online');

Route::get('/api/products-count', function (Request $request) {
    /** @var AuthSession */
    $session = $request->get('shopifySession'); // Provided by the shopify.auth middleware, guaranteed to be active

    $client = new Rest($session->getShop(), $session->getAccessToken());
    $result = $client->get('products/count');

    return response($result->getDecodedBody());
})->middleware('shopify.auth:online');

Route::post('/api/webhooks', function (Request $request) {
    try {
        $topic = $request->header(HttpHeaders::X_SHOPIFY_TOPIC, '');

        $response = Registry::process($request->header(), $request->getContent());
        if (!$response->isSuccess()) {
            Log::error("Failed to process '$topic' webhook: {$response->getErrorMessage()}");
            return response()->json(['message' => "Failed to process '$topic' webhook"], 500);
        }
    } catch (\Exception $e) {
        Log::error("Got an exception when handling '$topic' webhook: {$e->getMessage()}");
        return response()->json(['message' => "Got an exception when handling '$topic' webhook"], 500);
    }
});
