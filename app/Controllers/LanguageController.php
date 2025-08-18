<?php

namespace App\Controllers;

use Core\Helpers\TranslationHelper;
use Core\Http\Response;
use Core\Render\BaseController;

class LanguageController extends BaseController
{
    /**
     * Přepnutí jazyka
     */
    public function switch(string $locale): void
    {
        if (TranslationHelper::isLocaleSupported($locale)) {
            TranslationHelper::setLocale($locale);

            // Přesměrování zpět na předchozí stránku
            $referer = $_SERVER['HTTP_REFERER'] ?? '/';
            $this->redirect($referer);
        } else {
            // Neplatný jazyk - přesměrování na domovskou stránku
            $this->redirect('/');
        }
    }

    /**
     * Získání seznamu podporovaných jazyků
     */
    public function getSupportedLanguages(): Response\JsonResponse
    {
        $locales = TranslationHelper::getSupportedLocales();
        $currentLocale = TranslationHelper::getLocale();

        $languages = [];
        foreach ($locales as $code => $info) {
            $languages[] = [
                'code' => $code,
                'name' => $info['name'],
                'native' => $info['native'],
                'flag' => $info['flag'],
                'direction' => $info['direction'],
                'is_current' => $code === $currentLocale
            ];
        }

        return $this->jsonSuccess($languages);
    }

    /**
     * Získání chybějících překladů
     */
    public function getMissingTranslations(): Response\JsonResponse
    {
        $missing = TranslationHelper::getMissingTranslations();

        return $this->jsonSuccess([
            'missing_count' => count($missing),
            'missing_keys' => array_keys($missing),
            'current_locale' => TranslationHelper::getLocale()
        ]);
    }

    /**
     * Export překladů pro daný jazyk
     */
    public function export(string $locale): Response\JsonResponse
    {
        if (!TranslationHelper::isLocaleSupported($locale)) {
            return $this->jsonError('Neplatný jazyk');
        }

        $translations = TranslationHelper::getAllTranslations('messages', $locale);

        return $this->jsonSuccess([
            'locale' => $locale,
            'translations' => $translations,
            'count' => count($translations)
        ]);
    }

    /**
     * Import překladů
     */
    public function import(): Response\JsonResponse
    {
        $locale = $this->input('locale');
        $translations = $this->input('translations');

        if (!TranslationHelper::isLocaleSupported($locale)) {
            return $this->jsonError('Neplatný jazyk');
        }

        if (!is_array($translations)) {
            return $this->jsonError('Neplatný formát překladů');
        }

        // Zde by byla logika pro uložení překladů do souboru
        $filePath = APP_ROOT . "/resources/lang/{$locale}/messages.php";
        $content = "<?php\n\nreturn " . var_export($translations, true) . ";\n";

        if (file_put_contents($filePath, $content)) {
            return $this->jsonSuccess('Překlady byly úspěšně importovány');
        } else {
            return $this->jsonError('Chyba při ukládání překladů');
        }
    }

    /**
     * Nastavení jazyka uživatele
     */
    public function setUserLanguage(): Response\JsonResponse
    {
        // Získání dat z JSON těla požadavku
        $input = json_decode(file_get_contents('php://input'), true);
        $locale = $input['locale'] ?? $this->input('locale');

        debug_log("LanguageController: Požadavek na změnu jazyka na '{$locale}'");
        debug_log("LanguageController: Input data: " . json_encode($input));

        if (!TranslationHelper::isLocaleSupported($locale)) {
            debug_log("LanguageController: Neplatný jazyk '{$locale}'");
            return $this->jsonError('Neplatný jazyk');
        }

        TranslationHelper::setLocale($locale);
        debug_log("LanguageController: Jazyk úspěšně změněn na '{$locale}'");

        return $this->jsonSuccess([
            'message' => 'Jazyk byl úspěšně změněn',
            'locale' => $locale
        ]);
    }

    /**
     * Získání informací o aktuálním jazyce
     */
    public function getCurrentLanguage(): Response\JsonResponse
    {
        $currentLocale = TranslationHelper::getLocale();
        $localeInfo = TranslationHelper::getLocaleInfo($currentLocale);

        return $this->jsonSuccess([
            'locale' => $currentLocale,
            'info' => $localeInfo,
            'direction' => TranslationHelper::getTextDirection(),
            'is_rtl' => TranslationHelper::isRTL()
        ]);
    }
}
