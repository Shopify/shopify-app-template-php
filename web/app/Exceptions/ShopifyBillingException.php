<?php

namespace App\Exceptions;

use Exception;

class ShopifyBillingException extends Exception
{
    public array $errorData;

    public function __construct(string $message, array $errorData = null)
    {
        parent::__construct($message);

        $this->errorData = $errorData;
    }
}
