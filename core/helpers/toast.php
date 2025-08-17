<?php

/**
 * Helper funkce pro toast notification systém
 */

use Core\Notification\ToastNotification;

if (!function_exists('toast')) {
    /**
     * Přidá toast notifikaci
     */
    function toast(string $type, string $message, int $duration = 5000, array $options = []): void
    {
        ToastNotification::add($type, $message, $duration, $options);
    }
}

if (!function_exists('toast_success')) {
    /**
     * Přidá success toast notifikaci
     */
    function toast_success(string $message, int $duration = 5000, array $options = []): void
    {
        ToastNotification::success($message, $duration, $options);
    }
}

if (!function_exists('toast_error')) {
    /**
     * Přidá error toast notifikaci
     */
    function toast_error(string $message, int $duration = 5000, array $options = []): void
    {
        ToastNotification::error($message, $duration, $options);
    }
}

if (!function_exists('toast_warning')) {
    /**
     * Přidá warning toast notifikaci
     */
    function toast_warning(string $message, int $duration = 5000, array $options = []): void
    {
        ToastNotification::warning($message, $duration, $options);
    }
}

if (!function_exists('toast_info')) {
    /**
     * Přidá info toast notifikaci
     */
    function toast_info(string $message, int $duration = 5000, array $options = []): void
    {
        ToastNotification::info($message, $duration, $options);
    }
}

if (!function_exists('toast_ok')) {
    /**
     * Rychlá metoda pro success toast
     */
    function toast_ok(string $message, int $duration = 5000, array $options = []): void
    {
        ToastNotification::ok($message, $duration, $options);
    }
}

if (!function_exists('toast_fail')) {
    /**
     * Rychlá metoda pro error toast
     */
    function toast_fail(string $message, int $duration = 5000, array $options = []): void
    {
        ToastNotification::fail($message, $duration, $options);
    }
}

if (!function_exists('toast_warn')) {
    /**
     * Rychlá metoda pro warning toast
     */
    function toast_warn(string $message, int $duration = 5000, array $options = []): void
    {
        ToastNotification::warn($message, $duration, $options);
    }
}

if (!function_exists('render_toasts')) {
    /**
     * Vyrenderuje toast notifikace
     */
    function render_toasts(): string
    {
        return ToastNotification::render();
    }
}

if (!function_exists('render_toast_scripts')) {
    /**
     * Vyrenderuje toast notification skripty
     */
    function render_toast_scripts(): string
    {
        return ToastNotification::renderScripts();
    }
}

if (!function_exists('has_toasts')) {
    /**
     * Zkontroluje, zda existují toast notifikace
     */
    function has_toasts(): bool
    {
        return ToastNotification::hasAny();
    }
}

if (!function_exists('clear_toasts')) {
    /**
     * Vyčistí všechny toast notifikace
     */
    function clear_toasts(): void
    {
        ToastNotification::clear();
    }
}

if (!function_exists('toast_for_redirect')) {
    /**
     * Nastaví toast notifikace pro redirect
     */
    function toast_for_redirect(array $notifications): void
    {
        ToastNotification::setForRedirect($notifications);
    }
}

if (!function_exists('render_toasts')) {
    /**
     * Vyrenderuje toast notifikace (kompatibilita)
     */
    function render_toasts(): string
    {
        return ToastNotification::render();
    }
}

if (!function_exists('render_toast_script')) {
    /**
     * Vyrenderuje toast skripty (kompatibilita)
     */
    function render_toast_script(): string
    {
        return ToastNotification::renderScripts();
    }
}

if (!function_exists('render_toast_scripts')) {
    /**
     * Vyrenderuje toast notification skripty (nová funkce)
     */
    function render_toast_scripts(): string
    {
        return ToastNotification::renderScripts();
    }
}

if (!function_exists('render_flash')) {
    /**
     * Vyrenderuje flash notifikace (kompatibilita - přesměrováno na toast)
     */
    function render_flash(): string
    {
        return ToastNotification::render();
    }
}

if (!function_exists('render_flash_scripts')) {
    /**
     * Vyrenderuje flash skripty (kompatibilita - přesměrováno na toast)
     */
    function render_flash_scripts(): string
    {
        return ToastNotification::renderScripts();
    }
}
