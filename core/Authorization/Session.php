<?php

namespace Core\Authorization;

class Session
{
    protected bool $started = false;
    protected int $timeoutMinutes = 30;

    public function __construct()
    {
        $this->start();
        $this->checkTimeout();
    }

    public function start(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
            $this->started = true;
        }
    }

    public function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public function get(string $key, mixed $default = null): mixed
    {
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
        $_SESSION = [];
    }

    public function destroy(): void
    {
        $_SESSION = [];
        session_destroy();
    }

    public function regenerateId(bool $deleteOldSession = true): void
    {
        session_regenerate_id($deleteOldSession);
    }

    public function id(): string
    {
        return session_id();
    }

    // -----------------------------
    // Toast notifikace (místo flash zpráv)
    // -----------------------------

    public function flash(string $key, mixed $value): void
    {
        // Přesměrujeme na toast notifikace
        $this->toast($key, $value);
    }

    public function getFlash(string $key, mixed $default = null): mixed
    {
        // Pro kompatibilitu vracíme null, protože toast se zobrazuje automaticky
        return null;
    }

    public function toast(string $type, string $message, int $duration = 5000, array $options = []): void
    {
        // Použijeme Toast třídu pro notifikace
        \Core\Notification\Toast::$type($message, $duration, $options);
    }

    public function toastSuccess(string $message, int $duration = 5000, array $options = []): void
    {
        \Core\Notification\Toast::success($message, $duration, $options);
    }

    public function toastError(string $message, int $duration = 5000, array $options = []): void
    {
        \Core\Notification\Toast::error($message, $duration, $options);
    }

    public function toastWarning(string $message, int $duration = 5000, array $options = []): void
    {
        \Core\Notification\Toast::warning($message, $duration, $options);
    }

    public function toastInfo(string $message, int $duration = 5000, array $options = []): void
    {
        \Core\Notification\Toast::info($message, $duration, $options);
    }

    // -----------------------------
    // Timeout podle nečinnosti
    // -----------------------------

    protected function checkTimeout(): void
    {
        $now = time();

        if (isset($_SESSION['_last_activity']) && ($now - $_SESSION['_last_activity']) > $this->timeoutMinutes * 60) {
            $this->destroy();
            session_start(); // nové session po timeoutu
        }

        $_SESSION['_last_activity'] = $now;
    }
}