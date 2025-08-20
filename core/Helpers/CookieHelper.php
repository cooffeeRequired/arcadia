<?php

namespace Core\Helpers;

final class CookieHelper
{
    /**
     * Nastaví cookie.
     *
     * @param string               $name
     * @param mixed                $value
     * @param int|float|string     $ttl        Počet sekund, nebo řetězec pro strtotime (např. "+1 day"),
     *                                         "session" = do zavření prohlížeče, "infinity" = ~10 let.
     * @param bool                 $secure     Použít pouze přes HTTPS.
     * @param bool                 $force      Přepsat, i když již existuje.
     * @param string               $path
     * @param string               $domain
     * @param string               $sameSite   Lax|Strict|None
     */
    public static function set(
        string $name,
        mixed $value,
        int|float|string $ttl = 3600,
        bool $secure = false,
        bool $force = false,
        string $path = '/',
        string $domain = '',
        string $sameSite = 'Lax'
    ): void {
        if (!$force && isset($_COOKIE[$name])) {
            return;
        }

        // Výpočet expirace
        $expires = self::normalizeExpires($ttl);

        // Normalizace SameSite
        $sameSite = ucfirst(strtolower($sameSite));
        if (!in_array($sameSite, ['Lax', 'Strict', 'None'], true)) {
            $sameSite = 'Lax';
        }

        // Serializace hodnoty (umožní ukládat i pole/objekty)
        $stored = (is_array($value) || is_object($value))
            ? json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            : (string) $value;

        // Nastavení cookie (PHP 7.3+)
        setcookie(
            $name,
            $stored,
            [
                'expires'  => $expires,
                'path'     => $path,
                'domain'   => $domain ?: '',
                'secure'   => $secure,
                'httponly' => true,
                'samesite' => $sameSite,
            ]
        );

        // Okamžitý přístup v rámci requestu
        $_COOKIE[$name] = $stored;
    }

    /**
     * Získá hodnotu cookie. Pokud je v cookie JSON, automaticky jej dekóduje.
     *
     * @template T
     * @param string      $name
     * @param mixed|null  $default
     * @param ?string     $castTo   'int','float','bool','string' (volitelné)
     * @return mixed|T
     */
    public static function get(string $name, mixed $default = null, ?string $castTo = null): mixed
    {
        if (!array_key_exists($name, $_COOKIE)) {
            return $default;
        }

        $raw = $_COOKIE[$name];

        // Zkus JSON
        $decoded = json_decode($raw, true);
        $value = (json_last_error() === JSON_ERROR_NONE) ? $decoded : $raw;

        // Volitelné přetypování
        if ($castTo) {
            switch (strtolower($castTo)) {
                case 'int':    return (int) $value;
                case 'float':  return (float) $value;
                case 'bool':   return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? (bool) $value;
                case 'string': return is_string($value) ? $value : (string) (is_scalar($value) ? $value : json_encode($value));
            }
        }

        return $value;
    }

    /**
     * Smaže cookie.
     */
    public static function delete(
        string $name,
        string $path = '/',
        string $domain = '',
        bool $secure = false,
        string $sameSite = 'Lax'
    ): void {
        $sameSite = ucfirst(strtolower($sameSite));
        if (!in_array($sameSite, ['Lax', 'Strict', 'None'], true)) {
            $sameSite = 'Lax';
        }

        setcookie(
            $name,
            '',
            [
                'expires'  => time() - 3600,
                'path'     => $path,
                'domain'   => $domain ?: '',
                'secure'   => $secure,
                'httponly' => true,
                'samesite' => $sameSite,
            ]
        );

        unset($_COOKIE[$name]);
    }

    /**
     * Existuje cookie?
     */
    public static function has(string $name): bool
    {
        return array_key_exists($name, $_COOKIE);
    }

    /**
     * Normalizace expirace.
     */
    private static function normalizeExpires(int|float|string $ttl): int
    {
        if (is_numeric($ttl)) {
            $ttl = (int) $ttl;
            // 0 = session cookie
            return $ttl > 0 ? time() + $ttl : 0;
        }

        $ttl = trim(strtolower($ttl));
        if ($ttl === 'session') {
            return 0;
        }
        if ($ttl === 'infinity' || $ttl === 'infinite') {
            return time() + 10 * 365 * 24 * 60 * 60;
        }

        // podpora např. "+1 day", "2026-01-01 12:00:00"
        $ts = strtotime($ttl);
        return $ts !== false ? $ts : (time() + 3600);
    }
}
