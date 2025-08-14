<?php

namespace Core\Notification;

use Core\State\RedisContainer;

/**
 * Toast Notification systém s Redis perzistencí
 *
 * Použití:
 *
 * // Inicializace
 * ToastNotification::init();
 *
 * // Základní použití
 * ToastNotification::success('Operace byla úspěšná!');
 * ToastNotification::error('Nastala chyba!');
 * ToastNotification::warning('Pozor na toto!');
 * ToastNotification::info('Informace pro vás');
 *
 * // Render v šabloně
 * echo ToastNotification::render();
 * echo ToastNotification::renderScripts();
 */
final class ToastNotification
{
    private const string REDIS_KEY_PREFIX = 'toast_notifications:';
    private const string SESSION_KEY = 'toast_notifications';
    private static bool $initialized = false;
    private static string $position = 'bottom-right';
    private static string $outlineClass = '';
    private static string $contentClass = '';

    /**
     * Inicializuje toast notification systém
     */
    public static function init(): void
    {
        if (self::$initialized) {
            return;
        }

        self::$initialized = true;

        // Zkontrolujeme, zda máme notifikace v Redis
        $storedNotifications = self::getStoredNotifications();
        if (!empty($storedNotifications)) {
            // Přesuneme notifikace do session pro aktuální request
            $_SESSION[self::SESSION_KEY] = $storedNotifications;
            // Vyčistíme Redis
            self::clearStoredNotifications();
        }
    }

    /**
     * Přidá success toast notifikaci
     */
    public static function success(string $message, int $duration = 5000, array $options = []): void
    {
        self::addToast('success', $message, $duration, $options);
    }

    /**
     * Přidá error toast notifikaci
     */
    public static function error(string $message, int $duration = 5000, array $options = []): void
    {
        self::addToast('error', $message, $duration, $options);
    }

    /**
     * Přidá warning toast notifikaci
     */
    public static function warning(string $message, int $duration = 5000, array $options = []): void
    {
        self::addToast('warning', $message, $duration, $options);
    }

    /**
     * Přidá info toast notifikaci
     */
    public static function info(string $message, int $duration = 5000, array $options = []): void
    {
        self::addToast('info', $message, $duration, $options);
    }

    /**
     * Rychlá metoda pro success toast
     */
    public static function ok(string $message, int $duration = 5000, array $options = []): void
    {
        self::success($message, $duration, $options);
    }

    /**
     * Rychlá metoda pro error toast
     */
    public static function fail(string $message, int $duration = 5000, array $options = []): void
    {
        self::error($message, $duration, $options);
    }

    /**
     * Rychlá metoda pro warning toast
     */
    public static function warn(string $message, int $duration = 5000, array $options = []): void
    {
        self::warning($message, $duration, $options);
    }

    /**
     * Přidá custom toast notifikaci
     */
    public static function add(string $type, string $message, int $duration = 5000, array $options = []): void
    {
        self::addToast($type, $message, $duration, $options);
    }

    /**
     * Nastaví pozici toast notifikací
     */
    public static function setPosition(string $position): void
    {
        self::$position = $position;
    }

    /**
     * Nastaví outline třídu pro toast
     */
    public static function setOutlineClass(string $class): void
    {
        self::$outlineClass = $class;
    }

    /**
     * Nastaví content třídu pro toast
     */
    public static function setContentClass(string $class): void
    {
        self::$contentClass = $class;
    }

    /**
     * Přidá toast notifikaci
     */
    private static function addToast(string $type, string $message, int $duration, array $options = []): void
    {
        self::init();

        $toast = [
            'id' => uniqid('toast_'),
            'type' => $type,
            'message' => $message,
            'duration' => $duration,
            'timestamp' => time(),
            'position' => $options['position'] ?? self::$position,
            'outlineClass' => $options['outlineClass'] ?? self::$outlineClass,
            'contentClass' => $options['contentClass'] ?? self::$contentClass
        ];

        // Přidáme do session pro aktuální request
        if (!isset($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = [];
        }
        $_SESSION[self::SESSION_KEY][] = $toast;

        // Uložíme do Redis pro perzistenci přes redirecty
        self::storeNotifications();
    }

    /**
     * Získá všechny toast notifikace
     */
    public static function getAll(): array
    {
        self::init();

        $toasts = $_SESSION[self::SESSION_KEY] ?? [];
        $_SESSION[self::SESSION_KEY] = [];

        return $toasts;
    }

    /**
     * Zkontroluje, zda existují toast notifikace
     */
    public static function hasAny(): bool
    {
        self::init();
        return !empty($_SESSION[self::SESSION_KEY]);
    }

    /**
     * Vyčistí všechny toast notifikace
     */
    public static function clear(): void
    {
        $_SESSION[self::SESSION_KEY] = [];
        self::clearStoredNotifications();
    }

    /**
     * Vyrenderuje toast notifikace
     */
    public static function render(): string
    {
        $toasts = self::getAll();

        if (empty($toasts)) {
            return '';
        }

        $containerStyle = 'position: fixed; z-index: 9999;';
        $containerStyle .= match (self::$position) {
            'top-right' => ' top: 1rem; right: 1rem;',
            'top-left' => ' top: 1rem; left: 1rem;',
            'bottom-left' => ' bottom: 1rem; left: 1rem;',
            'center' => ' top: 50%; left: 50%; transform: translate(-50%, -50%);',
            default => ' bottom: 1rem; right: 1rem;',
        };

        $html = '<div id="toast-container" style="' . $containerStyle . '">';

        foreach ($toasts as $toast) {
            $html .= self::renderToastTemplate($toast);
        }

        $html .= '</div>';

        $toastsJson = json_encode($toasts, JSON_HEX_APOS | JSON_HEX_QUOT);
        $html .= '<script>window.initialToasts = ' . $toastsJson . ';</script>';

        return $html;
    }

    /**
     * Vyrenderuje JavaScript skripty pro toast notifikace
     */
    public static function renderScripts(): string
    {
        return "
        <script>
        // Fallback pro jQuery
        if (typeof jQuery === 'undefined') {
            var script = document.createElement('script');
            script.src = 'https://code.jquery.com/jquery-3.7.1.min.js';
            script.onload = function() {
                initToastNotifications();
            };
            document.head.appendChild(script);
        } else {
            initToastNotifications();
        }

        function initToastNotifications() {
            $(document).ready(function() {
                if (window.initialToasts && window.initialToasts.length > 0) {
                    window.initialToasts.forEach(function(toast, index) {
                        const toastElement = $('#' + toast.id);
                        if (toastElement.length === 0) {
                            return;
                        }

                        const duration = toast.duration || 5000;

                        setTimeout(function() {
                            toastElement.css({
                                'visibility': 'visible',
                                'transition': 'all 0.5s cubic-bezier(0.215, 0.61, 0.355, 1)'
                            });

                            setTimeout(function() {
                                toastElement.css({
                                    'opacity': '1',
                                    'transform': 'translateY(0)'
                                });
                            }, 50);
                        }, index * 150);

                        // Auto hide
                        setTimeout(function() {
                            removeToast(toast.id);
                        }, duration + (index * 150));

                        // Close button
                        toastElement.find('.toast-close').on('click', function() {
                            removeToast(toast.id);
                        });
                    });
                }
            });

            function removeToast(id) {
                const toast = $('#' + id);

                toast.css('transition', 'all 0.3s cubic-bezier(0.55, 0.055, 0.675, 0.19)');
                toast.css({
                    'opacity': '0',
                    'transform': 'translateY(-20px)'
                });

                setTimeout(function() {
                    toast.remove();
                }, 300);
            }
        }
        </script>";
    }

    /**
     * Vyrenderuje template pro toast notifikaci
     */
    private static function renderToastTemplate(array $toast): string
    {
        $type = $toast['type'];
        $message = $toast['message'];
        $id = $toast['id'];
        $position = $toast['position'];

        $borderClass = '';
        $iconColor = '';
        $icon = '';
        $bgColor = '';

        switch ($type) {
            case 'success':
                $borderClass = 'border-green-500';
                $iconColor = 'text-green-600';
                $bgColor = 'bg-green-50';
                $icon = '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>';
                break;
            case 'error':
                $borderClass = 'border-red-500';
                $iconColor = 'text-red-600';
                $bgColor = 'bg-red-50';
                $icon = '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>';
                break;
            case 'warning':
                $borderClass = 'border-yellow-500';
                $iconColor = 'text-yellow-500';
                $bgColor = 'bg-yellow-50';
                $icon = '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>';
                break;
            case 'info':
                $borderClass = 'border-blue-500';
                $iconColor = 'text-blue-600';
                $bgColor = 'bg-blue-50';
                $icon = '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>';
                break;
        }

        return '
        <div id="' . $id . '" class="' . $bgColor . ' border-l-4 ' . $borderClass . ' p-4 mb-4 shadow-lg rounded-lg max-w-sm w-full"
             style="visibility: hidden; opacity: 0; transform: translateY(-20px);"
             data-position="' . $position . '">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <div class="' . $iconColor . '">
                        ' . $icon . '
                    </div>
                </div>
                <div class="ml-3 flex-1">
                    <div class="text-sm font-medium text-gray-900 mb-1">' . self::getToastTitle($type) . '</div>
                    <div class="text-sm text-gray-600 break-words whitespace-normal">' . htmlspecialchars($message) . '</div>
                </div>
                <button class="toast-close ml-auto pl-3 -my-1.5 -mr-1.5 bg-transparent rounded-lg focus:ring-2 focus:ring-gray-400 p-1.5 hover:bg-gray-200 inline-flex h-8 w-8 text-gray-500 hover:text-gray-700 focus:outline-none">
                    <span class="sr-only">Zavřít</span>
                    <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        </div>';
    }

    /**
     * Získá název toast notifikace podle typu
     */
    private static function getToastTitle(string $type): string
    {
        return match ($type) {
            'success' => 'Úspěch',
            'error' => 'Chyba',
            'warning' => 'Upozornění',
            'info' => 'Informace',
            default => 'Notifikace',
        };
    }

    /**
     * Získá Redis klíč pro aktuální session
     */
    private static function getRedisKey(): string
    {
        $sessionId = session_id();
        if (empty($sessionId)) {
            // Pokud není session, použijeme IP adresu jako fallback
            $sessionId = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        }
        return self::REDIS_KEY_PREFIX . $sessionId;
    }

    /**
     * Uloží notifikace do Redis
     */
    private static function storeNotifications(): void
    {
        $notifications = $_SESSION[self::SESSION_KEY] ?? [];
        if (!empty($notifications)) {
            RedisContainer::set(self::getRedisKey(), $notifications, 300); // TTL 5 minut
        }
    }

    /**
     * Získá notifikace z Redis
     */
    private static function getStoredNotifications(): array
    {
        return RedisContainer::get(self::getRedisKey(), []);
    }

    /**
     * Vyčistí notifikace z Redis
     */
    private static function clearStoredNotifications(): void
    {
        RedisContainer::forget(self::getRedisKey());
    }

    /**
     * Nastaví notifikace pro redirect (uloží do Redis)
     */
    public static function setForRedirect(array $notifications): void
    {
        if (!empty($notifications)) {
            RedisContainer::set(self::getRedisKey(), $notifications, 300); // TTL 5 minut
        }
    }

    /**
     * Získá všechny notifikace včetně těch z Redis
     */
    public static function getAllWithStored(): array
    {
        $sessionNotifications = $_SESSION[self::SESSION_KEY] ?? [];
        $storedNotifications = self::getStoredNotifications();

        return array_merge($sessionNotifications, $storedNotifications);
    }

    /**
     * Vyčistí všechny notifikace pro všechny session (admin funkce)
     */
    public static function clearAllSessions(): void
    {
        $allKeys = RedisContainer::all();
        $prefix = self::REDIS_KEY_PREFIX;

        foreach ($allKeys as $key => $value) {
            if (str_starts_with($key, $prefix)) {
                RedisContainer::forget($key);
            }
        }
    }

    /**
     * Získá počet aktivních session s notifikacemi
     */
    public static function getActiveSessionsCount(): int
    {
        $allKeys = RedisContainer::all();
        $prefix = self::REDIS_KEY_PREFIX;
        $count = 0;

        foreach ($allKeys as $key => $value) {
            if (str_starts_with($key, $prefix)) {
                $count++;
            }
        }

        return $count;
    }
}
