<?php

declare(strict_types=1);

namespace App\Lib\Handlers\Privacy;

use Illuminate\Support\Facades\Log;
use Shopify\Webhooks\Handler;

/**
 * Store owners can request that data is deleted on behalf of a customer. When
 * this happens, Shopify invokes this privacy webhook.
 *
 * https://shopify.dev/docs/apps/webhooks/configuration/mandatory-webhooks#customers-redact
 */
class CustomersRedact implements Handler
{
    public function handle(string $topic, string $shop, array $body): void
    {
        Log::debug("Handling customer redaction request for $shop");
        // Payload has the following shape:
        // {
        //   "shop_id": 954889,
        //   "shop_domain": "{shop}.myshopify.com",
        //   "customer": {
        //     "id": 191167,
        //     "email": "john@example.com",
        //     "phone": "555-625-1199"
        //   },
        //   "orders_to_redact": [
        //     299938,
        //     280263,
        //     220458
        //   ]
        // }
    }
}
