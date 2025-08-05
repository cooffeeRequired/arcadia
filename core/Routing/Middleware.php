<?php

namespace Core\Routing;

class Middleware
{
    public static function auth(): void
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
    }

    public static function guest(): void
    {
        if (isset($_SESSION['user_id'])) {
            header('Location: /');
            exit;
        }
    }

    public static function admin(): void
    {
        self::auth();
        
        if ($_SESSION['user_role'] !== 'admin') {
            header('Location: /');
            exit;
        }
    }
} 