<?php

declare(strict_types=1);

namespace App\Lib\Handlers\Gdpr;

use Illuminate\Support\Facades\Log;
use Shopify\Webhooks\Handler;

/**
 * Customers can request their data from a store owner. When this happens,
 * Shopify invokes this webhook.
 *
 * https://shopify.dev/docs/apps/webhooks/configuration/mandatory-webhooks#customers-data_request
 */
class CustomersDataRequest implements Handler
{
    public function handle(string $topic, string $shop, array $body): void
    {
        Log::debug("Handling GDPR customer data request for $shop");
        // Payload has the following shape:
        // {
        //   "shop_id": 954889,
        //   "shop_domain": "{shop}.myshopify.com",
        //   "orders_requested": [
        //     299938,
        //     280263,
        //     220458
        //   ],
        //   "customer": {
        //     "id": 191167,
        //     "email": "john@example.com",
        //     "phone": "555-625-1199"
        //   },
        //   "data_request": {
        //     "id": 9999
        //   }
        // }
    }
}
