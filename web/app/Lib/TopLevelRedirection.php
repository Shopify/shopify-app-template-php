<?php

declare(strict_types=1);

namespace App\Lib;

use Illuminate\Http\Request;

class TopLevelRedirection
{
    public const REDIRECT_HEADER = 'X-Shopify-API-Request-Failure-Reauthorize';
    public const REDIRECT_URL_HEADER = 'X-Shopify-API-Request-Failure-Reauthorize-Url';

    public static function redirect(Request $request, $redirectUrl)
    {
        $bearerPresent = preg_match("/Bearer (.*)/", $request->header('Authorization', ''));
        if ($bearerPresent !== false) {
            return response('', 401, [
                self::REDIRECT_HEADER => '1',
                self::REDIRECT_URL_HEADER => $redirectUrl,
            ]);
        } else {
            return redirect($redirectUrl);
        }
    }
}
