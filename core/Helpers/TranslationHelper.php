<?php

namespace Core\Helpers;

use Core\Authorization\Session;
use Core\Facades\Container;
use DateTime;
use Illuminate\Translation\Translator;
use Illuminate\Translation\FileLoader;
use Illuminate\Filesystem\Filesystem;

class TranslationHelper
{
    private static ?Translator $translator = null;
    private static string $currentLocale = 'cs';
    private static string $fallbackLocale = 'en';

    /**
     * Inicializace překladového systému
     */
    public static function init(): void
    {
        if (self::$translator === null) {
            $session = Container::get('session');
            $userLocale = $session->get('user_locale');
            $cookieLocale = CookieHelper::get('cookie_locale', castTo: 'string');

            if ($userLocale && self::isLocaleSupported($userLocale)) {
                self::$currentLocale = $userLocale;
            } else if ($cookieLocale && self::isLocaleSupported($cookieLocale)) {
                self::$currentLocale = $cookieLocale;
            } else {
                self::$currentLocale = APP_CONFIGURATION['app_locale'] ?? 'cs';
            }

            $filesystem = new Filesystem();
            $loader = new FileLoader($filesystem, APP_ROOT . '/resources/lang');

            self::$translator = new Translator($loader, self::$currentLocale);
            self::$translator->setFallback(self::$fallbackLocale);
        }
    }

    /**
     * Překlad klíče
     */
    public static function trans(string $key, array $replace = [], ?string $locale = null): string
    {
        $session = Container::get('session', Session::class);
        $userLocale = $session->get('user_locale') ?? CookieHelper::get('cookie_locale', castTo: 'string');

        if ($userLocale && self::isLocaleSupported($userLocale) && $userLocale !== self::$currentLocale) {
            self::setLocale($userLocale);
        }

        $result = $locale && self::isLocaleSupported($locale)
            ? self::$translator->get($key, $replace, $locale)
            : self::$translator->get($key, $replace);

        if ($result === $key) {
            $filePath = resources('/lang/' . self::$currentLocale . '/messages.php');
            if (file_exists($filePath)) {
                $translations = require $filePath;
                if (isset($translations[$key])) {
                    $result = $translations[$key];
                }
            }
        }
        return $result;
    }

    /**
     * Zkratka pro trans()
     */
    public static function __(string $key, array $replace = [], ?string $locale = null): string
    {
        return self::trans($key, $replace, $locale);
    }

    /**
     * Nastavení aktuálního jazyka
     */
    public static function setLocale(string $locale): void
    {
        if (self::isLocaleSupported($locale)) {
            self::$currentLocale = $locale;

            $session = Container::get('session', Session::class);
            CookieHelper::set('cookie_locale', $locale);
            if ($session->has('user')) {
                $session->set('user_locale', $locale);
            }


            $filesystem = new Filesystem();
            $loader = new FileLoader($filesystem, APP_ROOT . '/resources/lang');
            self::$translator = new Translator($loader, $locale);
            self::$translator->setFallback(self::$fallbackLocale);
        }
    }

    /**
     * Získání aktuálního jazyka
     */
    public static function getLocale(): string
    {
        // Kontrola aktuálního jazyka z session
        $session = Container::get('session');
        $userLocale = $session->get('user_locale') ?? CookieHelper::get('cookie_locale', castTo: 'string');

        if ($userLocale && self::isLocaleSupported($userLocale)) {
            if ($userLocale !== self::$currentLocale) {
                self::setLocale($userLocale);
            }
            return $userLocale;
        }

        return self::$currentLocale;
    }

    /**
     * Kontrola, zda je jazyk podporován
     */
    public static function isLocaleSupported(string $locale): bool
    {
        $supportedLocales = self::getSupportedLocales();
        return array_key_exists($locale, $supportedLocales);
    }

    /**
     * Seznam podporovaných jazyků
     */
    public static function getSupportedLocales(): array
    {
        return [
            'cs' => [
                'name' => 'Čeština',
                'native' => 'Čeština',
                'flag' => '🇨🇿',
                'direction' => 'ltr'
            ],
            'en' => [
                'name' => 'English',
                'native' => 'English',
                'flag' => '🇺🇸',
                'direction' => 'ltr'
            ],
            'de' => [
                'name' => 'Deutsch',
                'native' => 'Deutsch',
                'flag' => '🇩🇪',
                'direction' => 'ltr'
            ],
            'sk' => [
                'name' => 'Slovenčina',
                'native' => 'Slovenčina',
                'flag' => '🇸🇰',
                'direction' => 'ltr'
            ],
            'pl' => [
                'name' => 'Polski',
                'native' => 'Polski',
                'flag' => '🇵🇱',
                'direction' => 'ltr'
            ]
        ];
    }

    /**
     * Získání informací o jazyku
     */
    public static function getLocaleInfo(string $locale): ?array
    {
        $locales = self::getSupportedLocales();
        return $locales[$locale] ?? null;
    }

    /**
     * Formátování data podle locale
     */
    public static function formatDate(DateTime $date, string $format = 'medium', ?string $locale = null): string
    {
        $locale = $locale ?: self::getLocale();

        $formats = [
            'short' => 'd.m.Y',
            'medium' => 'd.m.Y H:i',
            'long' => 'j. F Y',
            'full' => 'l, j. F Y'
        ];

        $dateFormat = $formats[$format] ?? $formats['medium'];

        // Lokalizované formátování podle jazyka
        if ($locale == 'en') {
            $formats = [
                'short' => 'm/d/Y',
                'medium' => 'm/d/Y H:i',
                'long' => 'F j, Y',
                'full' => 'l, F j, Y'
            ];
        }

        $dateFormat = $formats[$format] ?? $formats['medium'];
        return $date->format($dateFormat);
    }

    public static function formatNumber(float $number, int $decimals = 2, ?string $locale = null): string
    {
        $locale = $locale ?: self::getLocale();

        $formatters = [
            'cs' => ['decimal' => ',', 'thousands' => ' '],
            'en' => ['decimal' => '.', 'thousands' => ','],
            'de' => ['decimal' => ',', 'thousands' => '.'],
            'sk' => ['decimal' => ',', 'thousands' => ' '],
            'pl' => ['decimal' => ',', 'thousands' => ' ']
        ];

        $formatter = $formatters[$locale] ?? $formatters['en'];

        return number_format($number, $decimals, $formatter['decimal'], $formatter['thousands']);
    }

    public static function formatCurrency(float $amount, string $currency = 'CZK', ?string $locale = null): string
    {
        $locale = $locale ?: self::getLocale();

        $formattedNumber = self::formatNumber($amount, 2, $locale);

        $currencyFormats = [
            'cs' => '{amount} {currency}',
            'en' => '{currency}{amount}',
            'de' => '{amount} {currency}',
            'sk' => '{amount} {currency}',
            'pl' => '{amount} {currency}'
        ];

        $format = $currencyFormats[$locale] ?? $currencyFormats['en'];

        return str_replace(['{amount}', '{currency}'], [$formattedNumber, $currency], $format);
    }

    public static function getTextDirection(?string $locale = null): string
    {
        $locale = $locale ?: self::getLocale();
        $localeInfo = self::getLocaleInfo($locale);

        return $localeInfo['direction'] ?? 'ltr';
    }

    /**
     * Kontrola, zda je RTL jazyk
     */
    public static function isRTL(?string $locale = null): bool
    {
        return self::getTextDirection($locale) === 'rtl';
    }

    /**
     * Získání všech překladů pro daný namespace
     */
    public static function getAllTranslations(string $namespace = 'messages', ?string $locale = null): array
    {
        $locale = $locale ?: self::getLocale();
        $filePath = resources("/lang/{$locale}/{$namespace}.php");

        if (file_exists($filePath)) {
            return require $filePath;
        }

        return [];
    }

    /**
     * Kontrola, zda existuje překlad
     */
    public static function hasTranslation(string $key, ?string $locale = null): bool
    {
        $translation = self::trans($key, [], $locale);
        return $translation !== $key;
    }

    /**
     * Získání chybějících překladů
     */
    public static function getMissingTranslations(string $namespace = 'messages', string $sourceLocale = 'en'): array
    {
        $sourceTranslations = self::getAllTranslations($namespace, $sourceLocale);
        $currentTranslations = self::getAllTranslations($namespace, self::getLocale());

        return array_diff_key($sourceTranslations, $currentTranslations);
    }
}
