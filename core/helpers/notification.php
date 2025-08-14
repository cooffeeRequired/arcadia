<?php

/**
 * Helper funkce pro notification systém
 */

use Core\Notification\Flash;
use Core\Notification\Notification;

if (!function_exists('notification')) {
    /**
     * Přidá notifikaci
     */
    function notification(string $type, string $message, array $options = ['type' => 'flash']): void
    {
        Notification::add($type, $message, $options);
    }
}

if (!function_exists('notify_success')) {
    /**
     * Přidá success notifikaci
     */
    function notify_success(string $message, array $options = []): void
    {
        Notification::success($message, $options);
    }
}

if (!function_exists('notify_error')) {
    /**
     * Přidá error notifikaci
     */
    function notify_error(string $message, array $options = []): void
    {
        Notification::error($message, $options);
    }
}

if (!function_exists('notify_warning')) {
    /**
     * Přidá warning notifikaci
     */
    function notify_warning(string $message, array $options = []): void
    {
        Notification::warning($message, $options);
    }
}

if (!function_exists('notify_info')) {
    /**
     * Přidá info notifikaci
     */
    function notify_info(string $message, array $options = []): void
    {
        Notification::info($message, $options);
    }
}

if (!function_exists('render_notifications')) {
    /**
     * Vyrenderuje všechny notifikace
     */
    function render_notifications(): string
    {
        return Notification::renderAll();
    }
}

if (!function_exists('render_notification_scripts')) {
    /**
     * Vyrenderuje všechny notification skripty
     */
    function render_notification_scripts(): string
    {
        return Notification::renderAllScripts();
    }
}

if (!function_exists('render_toasts')) {
    /**
     * Vyrenderuje toast notifikace (kompatibilita)
     */
    function render_toasts(): string
    {
        return \Core\Traits\ToastTrait::renderToasts();
    }
}

if (!function_exists('render_toast_script')) {
    /**
     * Vyrenderuje toast skripty (kompatibilita)
     */
    function render_toast_script(): string
    {
        return \Core\Traits\ToastTrait::renderToastScript();
    }
}

if (!function_exists('render_flash')) {
    /**
     * Vyrenderuje flash notifikace
     */
    function render_flash(): string
    {
        return \Core\Traits\FlashTrait::renderFlash();
    }
}

if (!function_exists('render_flash_scripts')) {
    /**
     * Vyrenderuje flash skripty
     */
    function render_flash_scripts(): string
    {
        return \Core\Traits\FlashTrait::renderFlashScript();
    }
}
