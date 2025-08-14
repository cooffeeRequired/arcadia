<?php

namespace Core\Middleware;

use Core\Notification\ToastNotification;

/**
 * Middleware pro automatické zpracování toast notifikací
 *
 * Tento middleware automaticky inicializuje toast systém a zpracuje
 * notifikace uložené v Redis po redirectu.
 */
class ToastMiddleware
{
    /**
     * Zpracuje request a inicializuje toast systém
     */
    public static function handle(): void
    {
        // Inicializujeme toast notification systém
        ToastNotification::init();
    }

    /**
     * Zpracuje response a uloží notifikace pro redirect
     */
    public static function handleResponse(): void
    {
        // Pokud máme notifikace v session, uložíme je do Redis pro případný redirect
        if (ToastNotification::hasAny()) {
            $notifications = $_SESSION['toast_notifications'] ?? [];
            if (!empty($notifications)) {
                ToastNotification::setForRedirect($notifications);
            }
        }
    }

    /**
     * Automatické zpracování při ukončení skriptu
     */
    public static function autoHandle(): void
    {
        // Registrujeme shutdown funkci pro uložení notifikací
        register_shutdown_function([self::class, 'handleResponse']);

        // Inicializujeme systém
        self::handle();
    }
}
