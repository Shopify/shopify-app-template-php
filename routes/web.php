<?php

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Shopify\Auth\OAuth;
use Shopify\Auth\Session as AuthSession;
use Shopify\Clients\HttpHeaders;
use Shopify\Clients\Rest;
use Shopify\Context;
use Shopify\Laravel\Models\ShopifyApiSession;
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


Route::post('/graphql', function (Request $request) {
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

Route::get('/rest-example', function (Request $request) {
    /** @var AuthSession */
    $session = $request->get('shopifySession'); // Provided by the shopify.auth middleware, guaranteed to be active

    $client = new Rest($session->getShop(), $session->getAccessToken());
    $result = $client->get('products', [], ['limit' => 5]);

    return response($result->getDecodedBody());
})->middleware('shopify.auth:online');
