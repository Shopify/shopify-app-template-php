<?php

return [
    'api_key' => env('SHOPIFY_API_KEY', 'YOUR_API_KEY_HERE'),
    'api_secret' => env('SHOPIFY_API_SECRET', 'YOUR_API_SECRET_HERE'),
    'scopes' => env('SHOPIFY_SCOPES', [' ']),
];
