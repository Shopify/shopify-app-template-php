<?php

namespace App\Exceptions;

use Exception;
use Shopify\Clients\HttpResponse;

class ShopifyProductCreatorException extends Exception
{
    public HttpResponse $response;

    public function __construct(string $message, HttpResponse $response = null)
    {
        parent::__construct($message);

        $this->response = $response;
    }
}
