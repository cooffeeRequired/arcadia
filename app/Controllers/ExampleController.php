<?php

namespace App\Controllers;

use Core\Facades\Container;
use Core\Traits\Controller;

class ExampleController
{
    use Controller;

    private mixed $em;

    public function __construct()
    {
        $this->em = Container::get('doctrine.em');
    }

    /**
     * Příklad GET endpointu s view
     */
    public function index(): string
    {
        // Použití middleware
        $this->auth();
        
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
    public function api(): string
    {
        // Kontrola, zda je AJAX request
        if (!$this->isAjax()) {
            $this->status(400);
            return $this->json(['error' => 'Pouze AJAX requesty']);
        }

        // Získání POST dat
        $name = $this->input('name', '');
        $email = $this->input('email', '');

        if (empty($name) || empty($email)) {
            $this->status(422);
            return $this->json(['error' => 'Chybí povinná pole']);
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
    public function download(): string
    {
        $this->contentType('application/pdf');
        $this->header('Content-Disposition: attachment; filename="example.pdf"');
        
        // Simulace PDF obsahu
        $pdfContent = '%PDF-1.4...'; // Zde by byl skutečný PDF obsah
        
        return $this->render($pdfContent);
    }

    /**
     * Příklad s query parametry
     */
    public function search(): string
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