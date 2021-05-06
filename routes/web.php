<?php

use App\Models\Session;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Shopify\Auth\OAuth;

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
    $shop = $request->query('shop');
    $appInstalled = Session::where('shop', $shop)->exists();
    if($appInstalled){
        return view('unauthenticated');
    }
    return redirect("/login?shop=$shop");
});

Route::get('/auth/callback', function (Request $request) {
    OAuth::callback($request->cookie(), $request->query());
    $host = $request->query('host');
    $shop = $request->query('shop');
    return redirect("?" . http_build_query(['host' => $host, 'shop' => $shop]));
});
