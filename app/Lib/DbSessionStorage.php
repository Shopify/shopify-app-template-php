<?php

declare(strict_types=1);

namespace App\Lib;

use Exception;
use Shopify\Auth\AccessTokenOnlineUserInfo;
use Shopify\Auth\Session;
use Shopify\Auth\SessionStorage;

class DbSessionStorage implements SessionStorage
{

    public function loadSession(string $sessionId): Session|null
    {
        $dbSession = \App\Models\Session::where('session_id', $sessionId)->first();

        if ($dbSession) {
            $session = new Session(
                $dbSession->session_id,
                $dbSession->shop,
                $dbSession->is_online == 1,
                $dbSession->state
            );
            if ($dbSession->expires_at) {
                $session->setExpires($dbSession->expires_at);
            }
            if ($dbSession->access_token) {
                $session->setAccessToken($dbSession->access_token);
            }
            if ($dbSession->scope) {
                $session->setScope($dbSession->scope);
            }
            if ($dbSession->user_id) {
                $onlineAccessInfo = new AccessTokenOnlineUserInfo(
                    id: (int)$dbSession->user_id,
                    firstName: $dbSession->user_first_name,
                    lastName: $dbSession->user_last_name,
                    email: $dbSession->user_email,
                    emailVerified: $dbSession->user_email_verified == 1,
                    accountOwner: $dbSession->account_owner == 1,
                    locale: $dbSession->locale,
                    collaborator: $dbSession->collaborator == 1
                );
                $session->setOnlineAccessInfo($onlineAccessInfo);
            }
            return $session;
        }
        return null;
    }

    public function storeSession(Session $session): bool
    {
        $dbSession = \App\Models\Session::where('session_id', $session->getId())->first();
        if (!$dbSession) {
            $dbSession = new \App\Models\Session();
        }
        $dbSession->session_id = $session->getId();
        $dbSession->shop = $session->getShop();
        $dbSession->state = $session->getState();
        $dbSession->is_online = $session->isOnline();
        $dbSession->access_token = $session->getAccessToken();
        $dbSession->expires_at = $session->getExpires();
        $dbSession->scope = $session->getScope();
        if (!empty($session->getOnlineAccessInfo())) {
            $dbSession->user_id = $session->getOnlineAccessInfo()->getId();
            $dbSession->user_first_name = $session->getOnlineAccessInfo()->getFirstName();
            $dbSession->user_last_name = $session->getOnlineAccessInfo()->getLastName();
            $dbSession->user_email = $session->getOnlineAccessInfo()->getEmail();
            $dbSession->user_email_verified = $session->getOnlineAccessInfo()->isEmailVerified();
            $dbSession->account_owner = $session->getOnlineAccessInfo()->isAccountOwner();
            $dbSession->locale = $session->getOnlineAccessInfo()->getLocale();
            $dbSession->collaborator = $session->getOnlineAccessInfo()->isCollaborator();
        }
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
