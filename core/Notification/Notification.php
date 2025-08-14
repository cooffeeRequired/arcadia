<?php

namespace Core\Notification;

use Core\Traits\NotificationTrait;

/**
 * Moderní Notification systém s podporou Toast a Flash notifikací
 *
 * Použití:
 *
 * // Inicializace
 * Notification::init();
 *
 * // Základní použití
 * Notification::success('Operace byla úspěšná!');
 * Notification::error('Nastala chyba!');
 * Notification::warning('Pozor na toto!');
 * Notification::info('Informace pro vás');
 *
 * // Pouze toast
 * Notification::toast('Toast zpráva');
 * Notification::toastSuccess('Úspěch!');
 *
 * // Pouze flash
 * Notification::flash('Flash zpráva');
 * Notification::flashError('Chyba!');
 *
 * // Pokročilé nastavení
 * Notification::success('Zpráva', [
 *     'type' => 'toast', // 'toast', 'flash', 'both'
 *     'duration' => 3000,
 *     'toast' => ['position' => 'top-right'],
 *     'flash' => ['position' => 'bottom-center', 'autoHide' => false]
 * ]);
 *
 * // Render v šabloně
 * echo Notification::render();
 * echo Notification::renderAll();
 * echo Notification::renderAllScripts();
 */
class Notification
{
    use NotificationTrait;

    /**
     * Privátní konstruktor - třída je statická
     */
    private function __construct()
    {
        // Třída je statická
    }

    /**
     * Inicializuje notification systém s výchozím nastavením
     */
    public static function init(): void
    {
        self::initNotificationSystem();
    }

    // ===== ZÁKLADNÍ NOTIFIKACE =====

    /**
     * Rychlá metoda pro success notifikaci
     */
    public static function ok(string $message, array $options = []): void
    {
        self::success($message, $options);
    }

    /**
     * Rychlá metoda pro error notifikaci
     */
    public static function fail(string $message, array $options = []): void
    {
        self::error($message, $options);
    }

    /**
     * Rychlá metoda pro warning notifikaci
     */
    public static function warn(string $message, array $options = []): void
    {
        self::warning($message, $options);
    }

    /**
     * Přidá notifikaci s custom typem
     */
    public static function add(string $type, string $message, array $options = []): void
    {
        self::custom($type, $message, $options);
    }

    // ===== TOAST NOTIFIKACE =====

    /**
     * Rychlá metoda pro success toast
     */
    public static function okToast(string $message, int $duration = 5000, array $options = []): void
    {
        self::toastSuccess($message, $duration, $options);
    }

    /**
     * Rychlá metoda pro error toast
     */
    public static function failToast(string $message, int $duration = 5000, array $options = []): void
    {
        self::toastError($message, $duration, $options);
    }

    /**
     * Rychlá metoda pro warning toast
     */
    public static function warnToast(string $message, int $duration = 5000, array $options = []): void
    {
        self::toastWarning($message, $duration, $options);
    }

    /**
     * Přidá success toast
     */
    public static function toastSuccess(string $message, int $duration = 5000, array $options = []): void
    {
        self::addToast('success', $message, $duration, $options);
    }

    /**
     * Přidá error toast
     */
    public static function toastError(string $message, int $duration = 5000, array $options = []): void
    {
        self::addToast('error', $message, $duration, $options);
    }

    /**
     * Přidá warning toast
     */
    public static function toastWarning(string $message, int $duration = 5000, array $options = []): void
    {
        self::addToast('warning', $message, $duration, $options);
    }

    /**
     * Přidá info toast
     */
    public static function toastInfo(string $message, int $duration = 5000, array $options = []): void
    {
        self::addToast('info', $message, $duration, $options);
    }



    /**
     * Rychlá metoda pro success toast
     */
    public static function toast(string $message, int $duration = 5000, array $options = []): void
    {
        self::toastSuccess($message, $duration, $options);
    }









    // ===== FLASH NOTIFIKACE =====

    /**
     * Rychlá metoda pro success flash
     */
    public static function okFlash(string $message, array $options = []): void
    {
        self::flashSuccess($message, $options);
    }

    /**
     * Rychlá metoda pro error flash
     */
    public static function failFlash(string $message, array $options = []): void
    {
        self::flashError($message, $options);
    }

    /**
     * Rychlá metoda pro warning flash
     */
    public static function warnFlash(string $message, array $options = []): void
    {
        self::flashWarning($message, $options);
    }

    /**
     * Přidá success flash
     */
    public static function flashSuccess(string $message, array $options = []): void
    {
        self::addFlash('success', $message, $options);
    }

    /**
     * Přidá error flash
     */
    public static function flashError(string $message, array $options = []): void
    {
        self::addFlash('error', $message, $options);
    }

    /**
     * Přidá warning flash
     */
    public static function flashWarning(string $message, array $options = []): void
    {
        self::addFlash('warning', $message, $options);
    }

    /**
     * Přidá info flash
     */
    public static function flashInfo(string $message, array $options = []): void
    {
        self::addFlash('info', $message, $options);
    }



    /**
     * Rychlá metoda pro success flash
     */
    public static function flash(string $message, array $options = []): void
    {
        self::flashSuccess($message, $options);
    }







    /**
     * Vytvoří flash notifikaci, která se nezavře automaticky
     */
    public static function persistent(string $type, string $message, array $options = []): void
    {
        $options['autoHide'] = false;
        $options['dismissible'] = true;
        self::addFlash($type, $message, $options);
    }

    /**
     * Vytvoří flash notifikaci bez možnosti zavřít
     */
    public static function nonDismissible(string $type, string $message, array $options = []): void
    {
        $options['dismissible'] = false;
        $options['autoHide'] = true;
        self::addFlash($type, $message, $options);
    }

    // ===== KOMBINOVANÉ METODY =====

    /**
     * Zkontroluje, zda existují nějaké notifikace
     */
    public static function hasAny(): bool
    {
        return self::hasNotifications();
    }

    /**
     * Vyrenderuje všechny notifikace a skripty
     */
    public static function render(): string
    {
        $html = self::renderAll();
        $html .= self::renderAllScripts();
        return $html;
    }

    /**
     * Vyčistí všechny notifikace
     */
    public static function clear(): void
    {
        self::clearAll();
    }

    // ===== NASTAVENÍ =====

    /**
     * Nastaví globální nastavení
     */
    public static function configure(array $settings): void
    {
        self::updateNotificationSettings($settings);
    }

    /**
     * Získá aktuální nastavení
     */
    public static function getConfig(): array
    {
        return self::getNotificationSettings();
    }

    /**
     * Nastaví globální nastavení pro toast
     */
    public static function configureToast(array $settings): void
    {
        if (isset($settings['position'])) {
            self::setToastPosition($settings['position']);
        }
        if (isset($settings['outlineClass'])) {
            self::setToastOutlineClass($settings['outlineClass']);
        }
        if (isset($settings['contentClass'])) {
            self::setToastContentClass($settings['contentClass']);
        }
    }

    /**
     * Nastaví globální nastavení pro flash
     */
    public static function configureFlash(array $settings): void
    {
        if (isset($settings['position'])) {
            self::setFlashPosition($settings['position']);
        }
        if (isset($settings['containerClass'])) {
            self::setFlashContainerClass($settings['containerClass']);
        }
    }

    // ===== ALIAS METODY =====

    /**
     * Alias pro getAllToasts
     */
    public static function getToasts(): array
    {
        return self::getAllToasts();
    }

    /**
     * Alias pro getAllFlash
     */
    public static function getFlash(): array
    {
        return self::getAllFlash();
    }
}
