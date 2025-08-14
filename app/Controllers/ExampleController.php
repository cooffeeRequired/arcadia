<?php

namespace App\Controllers;

use Core\Http\Response;
use Core\Render\BaseController;

class ExampleController extends BaseController
{
    /**
     * Příklad GET endpointu s view
     */
    public function index(): Response\ViewResponse
    {
        // Použití middleware
        \Core\Routing\Middleware::auth($this->request);

        // Sdílení dat s view
        $this->share('title', 'Příklad stránka');

        // Renderování view s daty
        return $this->view('example.index', [
            'message' => 'Ahoj světe!',
            'users' => $this->em->getRepository('App\Entities\User')->findAll()
        ]);
    }

    /**
     * Příklad POST endpointu s JSON odpovědí
     */
    public function api(): Response\JsonResponse
    {
        // Kontrola, zda je AJAX request
        if (!$this->isAjax()) {
            return $this->json(['error' => 'Pouze AJAX requesty'], 400);
        }

        // Získání POST dat
        $name = $this->input('name', '');
        $email = $this->input('email', '');

        if (empty($name) || empty($email)) {
            return $this->json(['error' => 'Chybí povinná pole'], 422);
        }

        // Úspěšná odpověď
        return $this->json([
            'success' => true,
            'data' => [
                'name' => $name,
                'email' => $email
            ]
        ]);
    }

    /**
     * Příklad redirectu
     */
    public function redirectExample(): void
    {
        $this->session('info', 'Byl jste přesměrován');
        $this->redirect('/example');
    }

    /**
     * Příklad s custom headers
     */
    public function download(): Response\HtmlResponse
    {
        // Simulace PDF obsahu
        $pdfContent = '%PDF-1.4...'; // Zde by byl skutečný PDF obsah

        return new Response\HtmlResponse($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="example.pdf"'
        ]);
    }

    /**
     * Příklad s query parametry
     */
    public function search(): Response\ViewResponse
    {
        $query = $this->query('q', '');
        $page = (int) $this->query('page', 1);

        if (empty($query)) {
            $this->session('error', 'Zadejte vyhledávací dotaz');
            $this->redirect('/example');
        }

        // Zde by byla logika vyhledávání
        $results = []; // Simulace výsledků

        return $this->view('example.search', [
            'query' => $query,
            'page' => $page,
            'results' => $results
        ]);
    }
}
