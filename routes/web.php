<?php

use App\Models\Session;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Shopify\Context;
use Shopify\Utils;
use Shopify\Auth\OAuth;
use Shopify\Clients\HttpHeaders;
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

Route::get('/', function (Request $request) {
    $shop = Utils::sanitizeShopDomain($request->query('shop'));
    $host = $request->query('host');
    $appInstalled = Session::where('shop', $shop)->exists();
    if ($appInstalled) {
        return view('react', [
            'shop' => $shop,
            'host' => $host,
            'apiKey' => Context::$API_KEY
        ]);
    }
    return redirect("/login?shop=$shop");
});

Route::get('/login/toplevel', function (Request $request, Response $response) {
    $shop = Utils::sanitizeShopDomain($request->query('shop'));

    $response = new Response(view('top_level', [
        'apiKey' => Context::$API_KEY,
        'shop' => $shop,
        'hostName' => Context::$HOST_NAME,
    ]));

    $response->withCookie(
        cookie()->forever(name: 'shopify_top_level_oauth', value: '', sameSite:'strict', secure: true, httpOnly: true)
    );

    return $response;
});

Route::get('/login', function (Request $request) {
    $shop = Utils::sanitizeShopDomain($request->query('shop'));

    if (!$request->hasCookie('shopify_top_level_oauth')) {
        return redirect("/login/toplevel?shop=$shop");
    }

    $installUrl = OAuth::begin(
        $shop,
        '/auth/callback',
        true,
        function (Shopify\Auth\OAuthCookie $cookie) {
            Cookie::queue(
                $cookie->getName(),
                $cookie->getValue(),
                ceil(($cookie->getExpire() - time()) / 60),
                '/',
                Context::$HOST_NAME,
                $cookie->isSecure(),
                $cookie->isHttpOnly(),
                false,
                'Lax'
            );
            return true;
        }
    );

    return redirect($installUrl);
});

Route::get('/auth/callback', function (Request $request) {
    $session = OAuth::callback($request->cookie(), $request->query());

    $host = $request->query('host');
    $shop = Utils::sanitizeShopDomain($request->query('shop'));

    $response = Registry::register(
        path: '/webhooks',
        topic: Topics::APP_UNINSTALLED,
        shop: $shop,
        accessToken: $session->getAccessToken(),
    );
    if ($response->isSuccess()) {
        Log::debug("Registered APP_UNINSTALLED webhook for shop $shop");
    } else {
        Log::error(
            "Failed to register APP_UNINSTALLED webhook for shop $shop with response body: " .
            print_r($response->getBody(), true)
        );
    }

    return redirect("?" . http_build_query(['host' => $host, 'shop' => $shop]));
});

Route::post('/graphql', function (Request $request) {
    $result = Utils::graphqlProxy($request->header(), $request->cookie(), $request->getContent());
    return response($result->getDecodedBody())->withHeaders($result->getHeaders());
});

Route::post('/webhooks', function (Request $request) {
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
