<?php

namespace Core\Traits;

trait ToastTrait
{
    protected static array $toasts = [];
    protected static bool $initialized = false;
    protected static string $position = 'bottom-right';
    protected static string $outlineClass = '';
    protected static string $contentClass = '';

    /**
     * Přidá success toast notifikaci
     */
    public static function toastSuccess(string $message, int $duration = 5000, array $options = []): void
    {
        self::addToast('success', $message, $duration, $options);
    }

    /**
     * Přidá error toast notifikaci
     */
    public static function toastError(string $message, int $duration = 5000, array $options = []): void
    {
        self::addToast('error', $message, $duration, $options);
    }

    /**
     * Přidá warning toast notifikaci
     */
    public static function toastWarning(string $message, int $duration = 5000, array $options = []): void
    {
        self::addToast('warning', $message, $duration, $options);
    }

    /**
     * Přidá info toast notifikaci
     */
    public static function toastInfo(string $message, int $duration = 5000, array $options = []): void
    {
        self::addToast('info', $message, $duration, $options);
    }

    /**
     * Nastaví pozici toast notifikací
     */
    public static function setToastPosition(string $position): void
    {
        self::$position = $position;
    }

    /**
     * Nastaví outline třídu pro toast
     */
    public static function setToastOutlineClass(string $class): void
    {
        self::$outlineClass = $class;
    }

    /**
     * Nastaví content třídu pro toast
     */
    public static function setToastContentClass(string $class): void
    {
        self::$contentClass = $class;
    }

    /**
     * Přidá toast notifikaci
     */
    protected static function addToast(string $type, string $message, int $duration, array $options = []): void
    {
        if (!self::$initialized) {
            self::initToast();
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

    /**
     * Inicializuje toast systém
     */
    protected static function initToast(): void
    {
        self::$initialized = true;
        
        if (!isset($_SESSION['_toast_js_added'])) {
            $_SESSION['_toast_js_added'] = true;
        }
    }

    /**
     * Získá všechny toast notifikace
     */
    public static function getAllToasts(): array
    {
        $toasts = self::$toasts;
        self::$toasts = [];
        return $toasts;
    }

    /**
     * Zkontroluje, zda existují toast notifikace
     */
    public static function hasToasts(): bool
    {
        return !empty(self::$toasts);
    }

    /**
     * Vyčistí všechny toast notifikace
     */
    public static function clearToasts(): void
    {
        self::$toasts = [];
    }

    /**
     * Vyrenderuje toast notifikace
     */
    public static function renderToasts(): string
    {
        $toasts = self::getAllToasts();
        
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
            $html .= self::renderToastTemplate($toast['type'], $toast['message'], $toast['id'], $toast['position']);
        }
        
        $html .= '</div>';
        
        $toastsJson = json_encode($toasts, JSON_HEX_APOS | JSON_HEX_QUOT);
        $html .= '<script>window.initialToasts = ' . $toastsJson . ';</script>';
        
        // Debug informace
        $html .= '<!-- Debug: Toast count = ' . count($toasts) . ' -->';
        
        return $html;
    }

    /**
     * Získá název toast notifikace podle typu
     */
    protected static function getToastTitle(string $type): string
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
     * Vyrenderuje template pro toast notifikaci
     */
    protected static function renderToastTemplate(string $type, string $message, string $id, string $position = 'bottom-right'): string
    {
        $borderClass = '';
        $iconColor = '';
        $icon = '';
        
        switch ($type) {
            case 'success':
                $borderClass = 'border-l-4 border-green-500';
                $iconColor = 'text-green-600';
                $icon = '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>';
                break;
            case 'error':
                $borderClass = 'border-l-4 border-red-500';
                $iconColor = 'text-red-600';
                $icon = '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>';
                break;
            case 'warning':
                $borderClass = 'border-l-4 border-yellow-500';
                $iconColor = 'text-yellow-500';
                $icon = '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>';
                break;
            case 'info':
                $borderClass = 'border-l-4 border-blue-500';
                $iconColor = 'text-blue-600';
                $icon = '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>';
                break;
        }

        return '
        <div id="' . $id . '" class="bg-white shadow-lg rounded-lg pointer-events-auto ' . $borderClass . '" 
             style="margin-bottom: 0.5rem; visibility: hidden; opacity: 0; max-width: 450px; width: 100%;" 
             data-position="' . $position . '">
            <div class="p-4 relative">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <div class="' . $iconColor . '">
                            ' . $icon . '
                        </div>
                    </div>
                    <div class="ml-3 flex-1 pr-8">
                        <div class="text-sm font-medium text-gray-900 mb-1">' . self::getToastTitle($type) . '</div>
                        <div class="text-sm text-gray-600 break-words whitespace-normal">' . htmlspecialchars($message) . '</div>
                    </div>
                </div>
                <button class="toast-close absolute top-2 right-2 bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 z-10" style="position: absolute !important; top: 8px !important; right: 8px !important;" onclick="removeToast(\'' . $id . '\')">
                    <span class="sr-only">Zavřít</span>
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        </div>';
    }

    /**
     * Vyrenderuje JavaScript pro toast notifikace
     */
    public static function renderToastScript(): string
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
                console.log('Toast notifications initializing...');
                console.log('Initial toasts:', window.initialToasts);
                
                if (window.initialToasts && window.initialToasts.length > 0) {
                    window.initialToasts.forEach(function(toast, index) {
                        var toastElement = $('#' + toast.id);
                        console.log('Processing toast:', toast.id, toastElement.length);
                        
                        if (toastElement.length === 0) {
                            console.warn('Toast element not found:', toast.id);
                            return;
                        }
                        
                        var position = toastElement.data('position') || 'bottom-right';
                        
                        setTimeout(function() {
                            var initialTransform, finalTransform;
                            var animationProps = { opacity: 1 };
                            var animationOptions = { duration: 400, easing: 'easeOutCubic' };
                            
                            if (position.includes('right')) {
                                initialTransform = 'translateX(100%)';
                                finalTransform = 'translateX(0)';
                                animationProps.right = 0;
                            } else if (position.includes('left')) {
                                initialTransform = 'translateX(-100%)';
                                finalTransform = 'translateX(0)';
                                animationProps.left = 0;
                            } else if (position === 'center') {
                                initialTransform = 'scale(0.8) translateY(-20px)';
                                finalTransform = 'scale(1) translateY(0)';
                            }
                            
                            toastElement.css({
                                'visibility': 'visible',
                                'transform': initialTransform,
                                'opacity': '0'
                            });
                            
                            setTimeout(function() {
                                toastElement.css('transition', 'all 0.4s cubic-bezier(0.215, 0.61, 0.355, 1)');
                                toastElement.css({
                                    'transform': finalTransform,
                                    'opacity': '1'
                                });
                            }, 50);
                        }, index * 200);
                        
                        setTimeout(function() {
                            removeToast(toast.id);
                        }, toast.duration + (index * 200));
                        
                        toastElement.find('.toast-close').on('click', function() {
                            removeToast(toast.id);
                        });
                    });
                } else {
                    console.log('No toast notifications to show');
                }
            });
            
            window.removeToast = function(id) {
                var toast = $('#' + id);
                var position = toast.data('position') || 'bottom-right';
                
                var exitTransform;
                if (position.includes('right')) {
                    exitTransform = 'translateX(100%)';
                } else if (position.includes('left')) {
                    exitTransform = 'translateX(-100%)';
                } else if (position === 'center') {
                    exitTransform = 'scale(0.8) translateY(-20px)';
                }
                
                toast.css('transition', 'all 0.3s cubic-bezier(0.55, 0.055, 0.675, 0.19)');
                toast.css({
                    'transform': exitTransform,
                    'opacity': '0'
                });
                
                setTimeout(function() {
                    toast.remove();
                }, 300);
            };
            
            if (!$.easing.easeOutCubic) {
                $.extend($.easing, {
                    easeOutCubic: function(x) {
                        return 1 - Math.pow(1 - x, 3);
                    }
                });
            }
        }
        </script>";
    }
} 