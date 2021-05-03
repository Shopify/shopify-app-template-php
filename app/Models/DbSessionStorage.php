<?php

declare(strict_types=1);

namespace App\Models;

use Exception;
use Shopify\Auth\Session;
use Shopify\Auth\SessionStorage;

class DbSessionStorage implements SessionStorage
{

    public function loadSession(string $sessionId): Session|null
    {
        $dbSession = \App\Models\Session::where('session_id', $sessionId)->first();

        if ($dbSession){
            return new Session(
                $dbSession->session_id,
                $dbSession->shop,
                $dbSession->is_online == 1,
                $dbSession->state
            );
        }
        return null;
    }

    public function storeSession(Session $session): bool
    {
        $dbSession = new \App\Models\Session();
        $dbSession->session_id = $session->getId();
        $dbSession->shop = $session->getShop();
        $dbSession->state = $session->getState();
        $dbSession->is_online = $session->isOnline();
        try {
            return $dbSession->save();
        } catch (Exception) {
            return false;
        }
    }

    public function deleteSession(string $sessionId): bool
    {
        return \App\Models\Session::where('session_id', $sessionId)->delete() === 1;
    }
}
