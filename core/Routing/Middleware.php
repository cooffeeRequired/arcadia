<?php

namespace Core\Routing;

use Core\Http\Request;
use Exception;

class Middleware
{
    public static function auth(Request $request): void
    {
        debug_log("Auth middleware called for: " . $request->getUri());

        $user = $request->getSession()->get('user');
        debug_log("Session user data: " . json_encode($user));
        debug_log("Raw session data: " . json_encode($_SESSION));

        if ($user === null || !isset($user['id'])) {
            debug_log("User not authenticated, redirecting to login...");
            header('Location: /login');
            exit;
        }

        debug_log("User authenticated: " . $user['name']);
    }

    public static function guest(Request $request): void
    {
        $user = $request->getSession()->get('user');

        if ($user !== null && isset($user['id'])) {
            header('Location: /');
            exit;
        }
    }
}
