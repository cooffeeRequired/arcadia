<?php

/**
 * Helper funkce pro notification systém
 */

if (!function_exists('notification')) {
    /**
     * Přidá notifikaci
     */
    function notification(string $type, string $message, array $options = []): void
    {
        \Core\Notification\Notification::add($type, $message, $options);
    }
}

if (!function_exists('notify_success')) {
    /**
     * Přidá success notifikaci
     */
    function notify_success(string $message, array $options = []): void
    {
        \Core\Notification\Notification::success($message, $options);
    }
}

if (!function_exists('notify_error')) {
    /**
     * Přidá error notifikaci
     */
    function notify_error(string $message, array $options = []): void
    {
        \Core\Notification\Notification::error($message, $options);
    }
}

if (!function_exists('notify_warning')) {
    /**
     * Přidá warning notifikaci
     */
    function notify_warning(string $message, array $options = []): void
    {
        \Core\Notification\Notification::warning($message, $options);
    }
}

if (!function_exists('notify_info')) {
    /**
     * Přidá info notifikaci
     */
    function notify_info(string $message, array $options = []): void
    {
        \Core\Notification\Notification::info($message, $options);
    }
}

if (!function_exists('toast')) {
    /**
     * Přidá toast notifikaci
     */
    function toast(string $message, int $duration = 5000, array $options = []): void
    {
        \Core\Notification\Toast::success($message, $duration, $options);
    }
}

if (!function_exists('toast_success')) {
    /**
     * Přidá success toast notifikaci
     */
    function toast_success(string $message, int $duration = 5000, array $options = []): void
    {
        \Core\Notification\Toast::success($message, $duration, $options);
    }
}

if (!function_exists('toast_error')) {
    /**
     * Přidá error toast notifikaci
     */
    function toast_error(string $message, int $duration = 5000, array $options = []): void
    {
        \Core\Notification\Toast::error($message, $duration, $options);
    }
}

if (!function_exists('toast_warning')) {
    /**
     * Přidá warning toast notifikaci
     */
    function toast_warning(string $message, int $duration = 5000, array $options = []): void
    {
        \Core\Notification\Toast::warning($message, $duration, $options);
    }
}

if (!function_exists('toast_info')) {
    /**
     * Přidá info toast notifikaci
     */
    function toast_info(string $message, int $duration = 5000, array $options = []): void
    {
        \Core\Notification\Toast::info($message, $duration, $options);
    }
}

if (!function_exists('flash')) {
    /**
     * Přidá flash notifikaci
     */
    function flash(string $message, array $options = []): void
    {
        \Core\Notification\Flash::success($message, $options);
    }
}

if (!function_exists('flash_success')) {
    /**
     * Přidá success flash notifikaci
     */
    function flash_success(string $message, array $options = []): void
    {
        \Core\Notification\Flash::success($message, $options);
    }
}

if (!function_exists('flash_error')) {
    /**
     * Přidá error flash notifikaci
     */
    function flash_error(string $message, array $options = []): void
    {
        \Core\Notification\Flash::error($message, $options);
    }
}

if (!function_exists('flash_warning')) {
    /**
     * Přidá warning flash notifikaci
     */
    function flash_warning(string $message, array $options = []): void
    {
        \Core\Notification\Flash::warning($message, $options);
    }
}

if (!function_exists('flash_info')) {
    /**
     * Přidá info flash notifikaci
     */
    function flash_info(string $message, array $options = []): void
    {
        \Core\Notification\Flash::info($message, $options);
    }
}

if (!function_exists('flash_persistent')) {
    /**
     * Přidá persistent flash notifikaci
     */
    function flash_persistent(string $type, string $message, array $options = []): void
    {
        \Core\Notification\Flash::persistent($type, $message, $options);
    }
}

if (!function_exists('flash_non_dismissible')) {
    /**
     * Přidá non-dismissible flash notifikaci
     */
    function flash_non_dismissible(string $type, string $message, array $options = []): void
    {
        \Core\Notification\Flash::nonDismissible($type, $message, $options);
    }
}

if (!function_exists('render_notifications')) {
    /**
     * Vyrenderuje všechny notifikace
     */
    function render_notifications(): string
    {
        return \Core\Notification\Notification::renderAll();
    }
}

if (!function_exists('render_notification_scripts')) {
    /**
     * Vyrenderuje všechny notification skripty
     */
    function render_notification_scripts(): string
    {
        return \Core\Notification\Notification::renderAllScripts();
    }
}

if (!function_exists('render_toasts')) {
    /**
     * Vyrenderuje toast notifikace (kompatibilita)
     */
    function render_toasts(): string
    {
        return \Core\Notification\Toast::render();
    }
}

if (!function_exists('render_toast_script')) {
    /**
     * Vyrenderuje toast skripty (kompatibilita)
     */
    function render_toast_script(): string
    {
        return \Core\Notification\Toast::render();
    }
}

if (!function_exists('render_flash')) {
    /**
     * Vyrenderuje flash notifikace
     */
    function render_flash(): string
    {
        return \Core\Notification\Flash::render();
    }
}

if (!function_exists('render_flash_scripts')) {
    /**
     * Vyrenderuje flash skripty
     */
    function render_flash_scripts(): string
    {
        return \Core\Notification\Flash::render();
    }
} 