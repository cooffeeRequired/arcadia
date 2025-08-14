<?php

namespace Core\Traits;

trait FlashTrait
{
    protected static array $flashMessages = [];
    protected static bool $flashInitialized = false;
    protected static string $flashPosition = 'top-center';
    protected static string $flashContainerClass = '';

    /**
     * Přidá success flash notifikaci
     */
    public static function flashSuccess(string $message, array $options = []): void
    {
        self::addFlash('success', $message, $options);
    }

    /**
     * Přidá error flash notifikaci
     */
    public static function flashError(string $message, array $options = []): void
    {
        self::addFlash('error', $message, $options);
    }

    /**
     * Přidá warning flash notifikaci
     */
    public static function flashWarning(string $message, array $options = []): void
    {
        self::addFlash('warning', $message, $options);
    }

    /**
     * Přidá info flash notifikaci
     */
    public static function flashInfo(string $message, array $options = []): void
    {
        self::addFlash('info', $message, $options);
    }

    /**
     * Nastaví pozici flash notifikací
     */
    public static function setFlashPosition(string $position): void
    {
        self::$flashPosition = $position;
    }

    /**
     * Nastaví container třídu pro flash
     */
    public static function setFlashContainerClass(string $class): void
    {
        self::$flashContainerClass = $class;
    }

    /**
     * Přidá flash notifikaci
     */
    protected static function addFlash(string $type, string $message, array $options = []): void
    {
        if (!self::$flashInitialized) {
            self::initFlash();
        }

        $flash = [
            'id' => uniqid('flash_'),
            'type' => $type,
            'message' => $message,
            'timestamp' => time(),
            'position' => $options['position'] ?? self::$flashPosition,
            'containerClass' => $options['containerClass'] ?? self::$flashContainerClass,
            'dismissible' => $options['dismissible'] ?? true,
            'autoHide' => $options['autoHide'] ?? true,
            'duration' => $options['duration'] ?? 5000
        ];

        // Uloží flash zprávu do session
        if (!isset($_SESSION['_flash_messages'])) {
            $_SESSION['_flash_messages'] = [];
        }
        $_SESSION['_flash_messages'][] = $flash;


        // Také přidá do statické proměnné pro aktuální request
        self::$flashMessages[] = $flash;
    }

    /**
     * Získá URL s flash ID pro redirect
     */
    public static function getRedirectUrl(string $url): string
    {
        // Zkontroluj, zda existují flash zprávy v session
        if (!isset($_SESSION['_flash_messages']) || empty($_SESSION['_flash_messages'])) {
            return $url;
        }

        // Uloží flash zprávy do dočasného úložiště
        $flashId = uniqid('flash_redirect_');
        $tempDir = sys_get_temp_dir() . '/arcadia_flash';
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $filename = $tempDir . '/' . $flashId . '.json';
        $data = [
            'flash' => $_SESSION['_flash_messages'],
            'created' => time(),
            'expires' => time() + 300 // 5 minut
        ];

        file_put_contents($filename, json_encode($data));

        // Přidá flash ID do URL
        $separator = strpos($url, '?') !== false ? '&' : '?';
        return $url . $separator . 'flash_id=' . $flashId;
    }

    /**
     * Načte flash zprávy z URL parametru
     */
    protected static function loadFlashFromUrl(): void
    {
        $flashId = $_GET['flash_id'] ?? null;
        if (!$flashId) {
            return;
        }

        $tempDir = sys_get_temp_dir() . '/arcadia_flash';
        $filename = $tempDir . '/' . $flashId . '.json';

        if (file_exists($filename)) {
            $data = json_decode(file_get_contents($filename), true);

            if ($data && isset($data['flash']) && $data['expires'] > time()) {
                self::$flashMessages = array_merge(self::$flashMessages, $data['flash']);
            }

            // Smaže soubor
            unlink($filename);
        }
    }

    /**
     * Inicializuje flash systém
     */
    protected static function initFlash(): void
    {
        self::$flashInitialized = true;



        // Načte flash zprávy ze session
        if (isset($_SESSION['_flash_messages']) && is_array($_SESSION['_flash_messages'])) {
            self::$flashMessages = array_merge(self::$flashMessages, $_SESSION['_flash_messages']);
            // Vyčistí session flash zprávy po načtení
            unset($_SESSION['_flash_messages']);
        }

        // Načte flash z URL parametrů
        self::loadFlashFromUrl();

        if (!isset($_SESSION['_flash_js_added'])) {
            $_SESSION['_flash_js_added'] = true;
        }
    }

    /**
     * Získá všechny flash notifikace
     */
    public static function getAllFlash(): array
    {
        if (!self::$flashInitialized) {
            self::initFlash();
        }

        $flash = self::$flashMessages;
        self::$flashMessages = [];
        return $flash;
    }

    /**
     * Zkontroluje, zda existují flash notifikace
     */
    public static function hasFlash(): bool
    {
        if (!self::$flashInitialized) {
            self::initFlash();
        }

        return !empty(self::$flashMessages);
    }

    /**
     * Vyčistí všechny flash notifikace
     */
    public static function clearFlash(): void
    {
        self::$flashMessages = [];
        if (isset($_SESSION['_flash_messages'])) {
            unset($_SESSION['_flash_messages']);
        }
    }

    /**
     * Vyrenderuje flash notifikace
     */
    public static function renderFlash(): string
    {
        $flash = self::getAllFlash();

        if (empty($flash)) {
            return '';
        }

        $containerStyle = 'position: fixed; z-index: 10000; width: 100%; pointer-events: none;';
        $containerStyle .= match (self::$flashPosition) {
            'top-right' => ' top: 1rem; right: 1rem; width: auto;',
            'top-left' => ' top: 1rem; left: 1rem; width: auto;',
            'bottom-right' => ' bottom: 1rem; right: 1rem; width: auto;',
            'bottom-left' => ' bottom: 1rem; left: 1rem; width: auto;',
            'bottom-center' => ' bottom: 1rem; left: 50%; transform: translateX(-50%); width: auto; max-width: 600px;',
            default => ' top: 1rem; left: 50%; transform: translateX(-50%); max-width: 600px;',
        };

        $html = '<div id="flash-container" style="' . $containerStyle . '">';

        foreach ($flash as $flashMessage) {
            $html .= self::renderFlashTemplate($flashMessage);
        }

        $html .= '</div>';

        $flashJson = json_encode($flash, JSON_HEX_APOS | JSON_HEX_QUOT);
        $html .= '<script>window.initialFlash = ' . $flashJson . ';</script>';

        // Debug informace
        $html .= '<!-- Debug: Flash count = ' . count($flash) . ' -->';

        return $html;
    }

    /**
     * Získá název flash notifikace podle typu
     */
    protected static function getFlashTitle(string $type): string
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
     * Vyrenderuje template pro flash notifikaci
     */
    protected static function renderFlashTemplate(array $flash): string
    {
        $type = $flash['type'];
        $message = $flash['message'];
        $id = $flash['id'];
        $position = $flash['position'];
        $dismissible = $flash['dismissible'];
        $autoHide = $flash['autoHide'];
        $duration = $flash['duration'];

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

        $closeButton = '';
        if ($dismissible) {
            $closeButton = '
                <button class="flash-close ml-auto pl-3 -my-1.5 -mr-1.5 bg-transparent rounded-lg focus:ring-2 focus:ring-gray-400 p-1.5 hover:bg-gray-200 inline-flex h-8 w-8 text-gray-500 hover:text-gray-700 focus:outline-none" onclick="removeFlash(\'' . $id . '\')">
                    <span class="sr-only">Zavřít</span>
                    <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>';
        }

        return '
        <div id="' . $id . '" class="' . $bgColor . ' border-l-4 ' . $borderClass . ' p-4 mb-4 shadow-lg rounded-lg pointer-events-auto"
             style="visibility: hidden; opacity: 0; transform: translateY(-20px);"
             data-position="' . $position . '"
             data-auto-hide="' . ($autoHide ? 'true' : 'false') . '"
             data-duration="' . $duration . '">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <div class="' . $iconColor . '">
                        ' . $icon . '
                    </div>
                </div>
                <div class="ml-3 flex-1">
                    <div class="text-sm font-medium text-gray-900 mb-1">' . self::getFlashTitle($type) . '</div>
                    <div class="text-sm text-gray-600 break-words whitespace-normal">' . htmlspecialchars($message) . '</div>
                </div>
                ' . $closeButton . '
            </div>
        </div>';
    }

    /**
     * Vyrenderuje JavaScript pro flash notifikace
     */
    public static function renderFlashScript(): string
    {
        return "
        <script>
        if (typeof jQuery === 'undefined') {
            const script = document.createElement('script');
            script.src = 'https://code.jquery.com/jquery-3.7.1.min.js';
            script.onload = function() {
                initFlashNotifications();
            };
            document.head.appendChild(script);
        } else {
            initFlashNotifications();
        }

        function initFlashNotifications() {
            $(document).ready(function() {
                if (window.initialFlash && window.initialFlash.length > 0) {
                    window.initialFlash.forEach(function(flash, index) {
                        const flashElement = $('#' + flash.id);
                        if (flashElement.length === 0) {
                            return;
                        }

                        const position = flashElement.data('position') || 'top-center';
                        const autoHide = flash.autoHide || false;
                        const duration = parseInt(flashElement.data('duration')) || 5000;

                        setTimeout(function() {
                            flashElement.css({
                                'visibility': 'visible',
                                'transition': 'all 0.5s cubic-bezier(0.215, 0.61, 0.355, 1)'
                            });

                            setTimeout(function() {
                                flashElement.css({
                                    'opacity': '1',
                                    'transform': 'translateY(0)'
                                });
                            }, 50);
                        }, index * 150);

                        if (autoHide) {
                            setTimeout(function() {
                                removeFlash(flash.id);
                            }, duration + (index * 150));
                        }

                        flashElement.find('.flash-close').on('click', function() {
                            removeFlash(flash.id);
                        });
                    });
                } else {
                    console.log('No flash notifications to show');
                }
            });

            const removeFlash = function(id) {
                const flash = $('#' + id);

                console.log('removing')

                flash.css('transition', 'all 0.3s cubic-bezier(0.55, 0.055, 0.675, 0.19)');
                flash.css({
                    'opacity': '0',
                    'transform': 'translateY(-20px)'
                });

                setTimeout(function() {
                    flash.remove();
                }, 300);
            };
        }
        </script>";
    }
}
