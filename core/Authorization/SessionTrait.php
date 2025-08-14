<?php

namespace Core\Authorization;

trait SessionTrait
{
    public function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public function All(): array
    {
        return $_SESSION;
    }

        public function get(string $key, mixed $default = null): mixed
    {
        // Pokud používáme Redis session, data nejsou v $_SESSION
        // Musíme je načíst z session handleru
        if (session_status() === PHP_SESSION_ACTIVE && empty($_SESSION)) {
            // Zkus načíst data z session handleru pomocí session_id
            $sessionId = session_id();
            if ($sessionId) {
                // Zkus načíst data z Redis přímo
                $redis = \Core\Facades\Container::get('redis');
                if ($redis) {
                    $key = "arcadia:session:{$sessionId}";
                    $data = $redis->get($key);
                    if ($data) {
                        $sessionData = json_decode($data, true);
                        if ($sessionData && isset($sessionData['data'])) {
                            // Dekóduj session data
                            $decoded = session_decode($sessionData['data']);
                            if ($decoded) {
                                return $_SESSION[$key] ?? $default;
                            }
                        }
                    }
                }
            }
        }

        return $_SESSION[$key] ?? $default;
    }

    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public function clear(): void
    {
        session_unset();
    }

    public function destroy(): void
    {
        session_destroy();
    }

    public function id(): string
    {
        return session_id();
    }

    /**
     * Uloží session data
     */
    public function save(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
        }
    }
}
