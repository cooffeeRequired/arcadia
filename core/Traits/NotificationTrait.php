<?php

namespace Core\Traits;

trait NotificationTrait
{
    use ToastTrait, FlashTrait;

    protected static bool $notificationInitialized = false;
    protected static array $notificationSettings = [];

    /**
     * Inicializuje celý notification systém
     */
    public static function initNotificationSystem(array $settings = []): void
    {
        if (self::$notificationInitialized) {
            return;
        }

        self::$notificationInitialized = true;
        self::$notificationSettings = array_merge([
            'toast' => [
                'position' => 'bottom-right',
                'outlineClass' => '',
                'contentClass' => ''
            ],
            'flash' => [
                'position' => 'top-center',
                'containerClass' => '',
                'dismissible' => true,
                'autoHide' => true,
                'duration' => 5000
            ]
        ], $settings);

        // Aplikujeme nastavení
        if (isset(self::$notificationSettings['toast'])) {
            self::setToastPosition(self::$notificationSettings['toast']['position']);
            self::setToastOutlineClass(self::$notificationSettings['toast']['outlineClass']);
            self::setToastContentClass(self::$notificationSettings['toast']['contentClass']);
        }

        if (isset(self::$notificationSettings['flash'])) {
            self::setFlashPosition(self::$notificationSettings['flash']['position']);
            self::setFlashContainerClass(self::$notificationSettings['flash']['containerClass']);
        }
    }

    /**
     * Přidá success notifikaci (toast i flash)
     */
    public static function success(string $message, array $options = ['type' => 'toast']): void
    {
        $toastOptions = $options['toast'] ?? [];
        $flashOptions = $options['flash'] ?? [];

        if (!isset($options['type']) || $options['type'] === 'toast' || $options['type'] === 'both') {
            self::toastSuccess($message, $options['duration'] ?? 5000, $toastOptions);
        }

        if (!isset($options['type']) || $options['type'] === 'flash' || $options['type'] === 'both') {
            self::flashSuccess($message, $flashOptions);
        }
    }

    /**
     * Přidá error notifikaci (toast i flash)
     */
    public static function error(string $message, array $options = []): void
    {
        $toastOptions = $options['toast'] ?? [];
        $flashOptions = $options['flash'] ?? [];

        if (!isset($options['type']) || $options['type'] === 'toast' || $options['type'] === 'both') {
            self::toastError($message, $options['duration'] ?? 5000, $toastOptions);
        }

        if (!isset($options['type']) || $options['type'] === 'flash' || $options['type'] === 'both') {
            self::flashError($message, $flashOptions);
        }
    }

    /**
     * Přidá warning notifikaci (toast i flash)
     */
    public static function warning(string $message, array $options = []): void
    {
        $toastOptions = $options['toast'] ?? [];
        $flashOptions = $options['flash'] ?? [];

        if (!isset($options['type']) || $options['type'] === 'toast' || $options['type'] === 'both') {
            self::toastWarning($message, $options['duration'] ?? 5000, $toastOptions);
        }

        if (!isset($options['type']) || $options['type'] === 'flash' || $options['type'] === 'both') {
            self::flashWarning($message, $flashOptions);
        }
    }

    /**
     * Přidá info notifikaci (toast i flash)
     */
    public static function info(string $message, array $options = []): void
    {
        $toastOptions = $options['toast'] ?? [];
        $flashOptions = $options['flash'] ?? [];

        if (!isset($options['type']) || $options['type'] === 'toast' || $options['type'] === 'both') {
            self::toastInfo($message, $options['duration'] ?? 5000, $toastOptions);
        }

        if (!isset($options['type']) || $options['type'] === 'flash' || $options['type'] === 'both') {
            self::flashInfo($message, $flashOptions);
        }
    }

    /**
     * Zkontroluje, zda existují nějaké notifikace
     */
    public static function hasNotifications(): bool
    {
        return self::hasToasts() || self::hasFlash();
    }

    /**
     * Vyčistí všechny notifikace
     */
    public static function clearAll(): void
    {
        self::clearToasts();
        self::clearFlash();
    }

    /**
     * Vyrenderuje všechny notifikace
     */
    public static function renderAll(): string
    {
        $html = '';

        // Render toast notifikace
        if (self::hasToasts()) {
            $html .= self::renderToasts();
        }

        // Render flash notifikace
        if (self::hasFlash()) {
            $html .= self::renderFlash();
        }

        return $html;
    }

    /**
     * Vyrenderuje všechny JavaScript skripty
     */
    public static function renderAllScripts(): string
    {
        $html = '';

        // Render toast script
        if (self::hasToasts()) {
            $html .= self::renderToastScript();
        }

        // Render flash script
        if (self::hasFlash()) {
            $html .= self::renderFlashScript();
        }

        return $html;
    }

    /**
     * Získá nastavení notification systému
     */
    public static function getNotificationSettings(): array
    {
        return self::$notificationSettings;
    }

    /**
     * Aktualizuje nastavení notification systému
     */
    public static function updateNotificationSettings(array $settings): void
    {
        self::$notificationSettings = array_merge(self::$notificationSettings, $settings);

        // Aplikujeme nová nastavení
        if (isset($settings['toast'])) {
            if (isset($settings['toast']['position'])) {
                self::setToastPosition($settings['toast']['position']);
            }
            if (isset($settings['toast']['outlineClass'])) {
                self::setToastOutlineClass($settings['toast']['outlineClass']);
            }
            if (isset($settings['toast']['contentClass'])) {
                self::setToastContentClass($settings['toast']['contentClass']);
            }
        }

        if (isset($settings['flash'])) {
            if (isset($settings['flash']['position'])) {
                self::setFlashPosition($settings['flash']['position']);
            }
            if (isset($settings['flash']['containerClass'])) {
                self::setFlashContainerClass($settings['flash']['containerClass']);
            }
        }
    }

    /**
     * Rychlá metoda pro success toast
     */
    public static function toast(string $message, int $duration = 5000, array $options = []): void
    {
        self::toastSuccess($message, $duration, $options);
    }

    /**
     * Rychlá metoda pro success flash
     */
    public static function flash(string $message, array $options = []): void
    {
        self::flashSuccess($message, $options);
    }

    /**
     * Přidá custom notifikaci
     */
    public static function custom(string $type, string $message, array $options = []): void
    {
        $toastOptions = $options['toast'] ?? [];
        $flashOptions = $options['flash'] ?? [];

        if (!isset($options['type']) || $options['type'] === 'toast' || $options['type'] === 'both') {
            self::addToast($type, $message, $options['duration'] ?? 5000, $toastOptions);
        }

        if (!isset($options['type']) || $options['type'] === 'flash' || $options['type'] === 'both') {
            self::addFlash($type, $message, $flashOptions);
        }
    }
}
