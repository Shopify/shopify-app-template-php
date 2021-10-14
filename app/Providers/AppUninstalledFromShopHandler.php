<?php

namespace App\Providers;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Shopify\Laravel\Events\AppUninstalled;
use Shopify\Laravel\Models\ShopifyApiSession;

class AppUninstalledFromShopHandler
{
    public function handle(AppUninstalled $event)
    {
        ShopifyApiSession::where('shop', $event->getShop())->delete();
    }
}
