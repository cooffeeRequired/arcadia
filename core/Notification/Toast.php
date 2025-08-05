<?php

namespace Core\Notification;

class Toast
{
    protected static array $toasts = [];
    protected static bool $initialized = false;
    protected static string $position = 'bottom-right'; // bottom-right, top-right, top-left, bottom-left, center
    protected static string $outlineClass = '';
    protected static string $contentClass = '';

    public static function success(string $message, int $duration = 5000, array $options = []): void
    {
        self::add('success', $message, $duration, $options);
    }

    public static function error(string $message, int $duration = 5000, array $options = []): void
    {
        self::add('error', $message, $duration, $options);
    }

    public static function warning(string $message, int $duration = 5000, array $options = []): void
    {
        self::add('warning', $message, $duration, $options);
    }

    public static function info(string $message, int $duration = 5000, array $options = []): void
    {
        self::add('info', $message, $duration, $options);
    }

    public static function setPosition(string $position): void
    {
        self::$position = $position;
    }

    public static function setOutlineClass(string $class): void
    {
        self::$outlineClass = $class;
    }

    public static function setContentClass(string $class): void
    {
        self::$contentClass = $class;
    }

    protected static function add(string $type, string $message, int $duration, array $options = []): void
    {
        if (!self::$initialized) {
            self::init();
        }

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

        self::$toasts[] = $toast;
    }

    protected static function init(): void
    {
        self::$initialized = true;
        
        // Přidáme JavaScript pro toast notifikace do hlavičky
        if (!isset($_SESSION['_toast_js_added'])) {
            $_SESSION['_toast_js_added'] = true;
        }
    }

    public static function getAll(): array
    {
        $toasts = self::$toasts;
        self::$toasts = []; // Vyčistíme po načtení
        return $toasts;
    }

    public static function hasToasts(): bool
    {
        return !empty(self::$toasts);
    }

    public static function clear(): void
    {
        self::$toasts = [];
    }

    public static function render(): string
    {
        $toasts = self::getAll();
        
        if (empty($toasts)) {
            return '';
        }

        $toastsJson = json_encode($toasts, JSON_HEX_APOS | JSON_HEX_QUOT);
        
        $html = '<div id="toast-container" x-data="toastNotifications()" style="position: fixed; bottom: 1rem; right: 1rem; z-index: 9999;">';
        $html .= '<template x-for="(toast, index) in toasts" :key="toast.id">';
        $html .= self::renderToastAlpine();
        $html .= '</template>';
        $html .= '</div>';
        $html .= '<script>window.initialToasts = ' . $toastsJson . ';</script>';
        
        return $html;
    }



    protected static function renderToastAlpine(): string
    {
        return '
        <div 
            x-show="toast.visible" 
            x-transition:enter="transform transition-all duration-300 ease-out"
            x-transition:enter-start="translate-x-full opacity-0"
            x-transition:enter-end="translate-x-0 opacity-100"
            x-transition:leave="transform transition-all duration-300 ease-in"
            x-transition:leave-start="translate-x-0 opacity-100"
            x-transition:leave-end="translate-x-full opacity-0"
            class="max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto"
            :class="{
                \'border-l-4 border-green-500\': toast.type === \'success\',
                \'border-l-4 border-red-500\': toast.type === \'error\',
                \'border-l-4 border-yellow-500\': toast.type === \'warning\',
                \'border-l-4 border-blue-500\': toast.type === \'info\'
            }"
            style="margin-bottom: 0.5rem;"
        >
            <div class="p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <div :class="{
                            \'text-green-600\': toast.type === \'success\',
                            \'text-red-600\': toast.type === \'error\',
                            \'text-yellow-600\': toast.type === \'warning\',
                            \'text-blue-600\': toast.type === \'info\'
                        }">
                            <svg x-show="toast.type === \'success\'" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <svg x-show="toast.type === \'error\'" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                            <svg x-show="toast.type === \'warning\'" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            <svg x-show="toast.type === \'info\'" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-3 w-0 flex-1 pt-0.5">
                        <p class="text-sm font-medium text-gray-900" x-text="toast.message"></p>
                    </div>
                    <div class="ml-4 flex-shrink-0 flex">
                        <button @click="removeToast(toast)" class="toast-close bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <span class="sr-only">Zavřít</span>
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>';
    }



    public static function renderScript(): string
    {
        return "
        <script>
        function toastNotifications() {
            return {
                toasts: (window.initialToasts || []).map(toast => ({
                    ...toast,
                    visible: false
                })),
                
                init() {
                    // Zobrazíme toast notifikace postupně
                    this.toasts.forEach((toast, index) => {
                        setTimeout(() => {
                            toast.visible = true;
                        }, index * 200);
                        
                        // Auto-close
                        setTimeout(() => {
                            this.removeToast(toast);
                        }, toast.duration + (index * 200));
                    });
                },
                
                removeToast(toast) {
                    toast.visible = false;
                    setTimeout(() => {
                        this.toasts = this.toasts.filter(t => t.id !== toast.id);
                    }, 300);
                }
            }
        }
        </script>";
    }
} 