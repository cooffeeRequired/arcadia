<?php

namespace App\Controllers;

use Core\Http\Response\ViewResponse;
use Core\Render\BaseController;

class ErrorController extends BaseController
{
    /**
     * Zobrazí 404 chybovou stránku
     */
    public function showNotFound(): ViewResponse
    {
        return $this->view('errors.404');
    }

    /**
     * Zobrazí 500 chybovou stránku
     */
    public function showServerError(\Exception $exception = null): ViewResponse
    {
        // Log chyby pro debugging (v produkci by se mělo logovat do souboru)
        if ($exception) {
            error_log("500 Error: " . $exception->getMessage() . " in " . $exception->getFile() . ":" . $exception->getLine());
        }
        return $this->view('errors.500');
    }

    /**
     * Zobrazí 403 chybovou stránku
     */
    public function showForbidden(): ViewResponse
    {
        return $this->view('errors.403');
    }

    /**
     * Zobrazí obecnou chybovou stránku
     */
    public function error(int $statusCode, string $message = null): ViewResponse
    {
        $errorData = [
            'code' => $statusCode,
            'title' => $this->getErrorTitle($statusCode),
            'message' => $message ?? $this->getErrorMessage($statusCode)
        ];

        return $this->view('errors.generic', $errorData);
    }

    /**
     * Získá nadpis pro daný status kód
     */
    private function getErrorTitle(int $statusCode): string
    {
        return match($statusCode) {
            400 => 'Špatný požadavek',
            401 => 'Neautorizovaný přístup',
            403 => 'Přístup zamítnut',
            404 => 'Stránka nenalezena',
            405 => 'Metoda není povolena',
            408 => 'Časový limit vypršel',
            429 => 'Příliš mnoho požadavků',
            500 => 'Interní chyba serveru',
            502 => 'Špatná brána',
            503 => 'Služba není dostupná',
            504 => 'Časový limit brány',
            default => 'Chyba'
        };
    }

    /**
     * Získá výchozí zprávu pro daný status kód
     */
    private function getErrorMessage(int $statusCode): string
    {
        return match($statusCode) {
            400 => 'Požadavek obsahuje neplatné údaje.',
            401 => 'Pro přístup k této stránce se musíte přihlásit.',
            403 => 'Nemáte oprávnění k přístupu k této stránce.',
            404 => 'Požadovaná stránka nebyla nalezena.',
            405 => 'Použitá metoda není pro tuto stránku povolena.',
            408 => 'Časový limit požadavku vypršel.',
            429 => 'Příliš mnoho požadavků. Zkuste to prosím později.',
            500 => 'Došlo k neočekávané chybě na serveru.',
            502 => 'Server dostal neplatnou odpověď od upstream serveru.',
            503 => 'Služba je dočasně nedostupná.',
            504 => 'Časový limit brány vypršel.',
            default => 'Došlo k neočekávané chybě.'
        };
    }
}
