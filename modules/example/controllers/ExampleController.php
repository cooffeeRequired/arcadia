<?php

namespace Modules\Example\Controllers;

use Modules\Example\Entities\ExampleItem;
use Core\Render\BaseController;
use Core\Http\Response\ViewResponse;
use Core\Http\Response\JsonResponse;
use Core\Modules\ModuleManager;
use Exception;

class ExampleController extends BaseController
{
    private ModuleManager $moduleManager;

    public function __construct()
    {
        parent::__construct();
        $this->moduleManager = new ModuleManager();
    }

    /**
     * Zobrazí seznam položek
     */
    public function index(): ViewResponse
    {
        // Kontrola, zda je modul dostupný
        if (!$this->moduleManager->isAvailable('example')) {
            $this->toastError('Modul Example není dostupný');
            $this->redirect('/');
            return $this->view('home.index', []);
        }

        $items = $this->em->getRepository(ExampleItem::class)->findAll();

        $data = [
            'items' => $items,
            'title' => 'Seznam příkladů'
        ];

        return $this->view('modules.example.index', $data);
    }

    /**
     * Zobrazí formulář pro vytvoření
     */
    public function create(): ViewResponse
    {
        if (!$this->moduleManager->hasPermission('example', 'create')) {
            $this->toastError('Nemáte oprávnění k vytvoření položky');
            $this->redirect('/example');
            return $this->view('modules.example.index', ['items' => []]);
        }

        return $this->view('modules.example.create', [
            'title' => 'Nový příklad'
        ]);
    }

    /**
     * Uloží novou položku
     */
    public function store(): JsonResponse
    {
        try {
            if (!$this->moduleManager->hasPermission('example', 'create')) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Nemáte oprávnění k vytvoření položky'
                ], 403);
            }

            $name = $this->input('name');
            $description = $this->input('description');

            if (empty($name)) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Název je povinný'
                ], 400);
            }

            $item = new ExampleItem();
            $item->setName($name)
                 ->setDescription($description)
                 ->setStatus('active');

            $this->em->persist($item);
            $this->em->flush();

            $this->toastSuccess('Položka byla úspěšně vytvořena');
            return new JsonResponse([
                'success' => true,
                'message' => 'Položka byla úspěšně vytvořena',
                'redirect' => '/example'
            ]);

        } catch (Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Chyba při vytváření položky: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Zobrazí detail položky
     */
    public function show(int $id): ViewResponse
    {
        if (!$this->moduleManager->hasPermission('example', 'view')) {
            $this->toastError('Nemáte oprávnění k zobrazení položky');
            $this->redirect('/example');
            return $this->view('modules.example.index', ['items' => []]);
        }

        $item = $this->em->getRepository(ExampleItem::class)->find($id);

        if (!$item) {
            $this->toastError('Položka nebyla nalezena');
            $this->redirect('/example');
            return $this->view('modules.example.index', ['items' => []]);
        }

        return $this->view('modules.example.show', [
            'item' => $item,
            'title' => 'Detail: ' . $item->getName()
        ]);
    }

    /**
     * Zobrazí formulář pro editaci
     */
    public function edit(int $id): ViewResponse
    {
        if (!$this->moduleManager->hasPermission('example', 'edit')) {
            $this->toastError('Nemáte oprávnění k editaci položky');
            $this->redirect('/example');
            return $this->view('modules.example.index', ['items' => []]);
        }

        $item = $this->em->getRepository(ExampleItem::class)->find($id);

        if (!$item) {
            $this->toastError('Položka nebyla nalezena');
            $this->redirect('/example');
            return $this->view('modules.example.index', ['items' => []]);
        }

        return $this->view('modules.example.edit', [
            'item' => $item,
            'title' => 'Editace: ' . $item->getName()
        ]);
    }

    /**
     * Aktualizuje položku
     */
    public function update(int $id): JsonResponse
    {
        try {
            if (!$this->moduleManager->hasPermission('example', 'edit')) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Nemáte oprávnění k editaci položky'
                ], 403);
            }

            $item = $this->em->getRepository(ExampleItem::class)->find($id);

            if (!$item) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Položka nebyla nalezena'
                ], 404);
            }

            $name = $this->input('name');
            $description = $this->input('description');
            $status = $this->input('status', 'active');

            if (empty($name)) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Název je povinný'
                ], 400);
            }

            $item->setName($name)
                 ->setDescription($description)
                 ->setStatus($status);

            $this->em->flush();

            $this->toastSuccess('Položka byla úspěšně aktualizována');
            return new JsonResponse([
                'success' => true,
                'message' => 'Položka byla úspěšně aktualizována',
                'redirect' => '/example'
            ]);

        } catch (Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Chyba při aktualizaci položky: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Smaže položku
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            if (!$this->moduleManager->hasPermission('example', 'delete')) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Nemáte oprávnění ke smazání položky'
                ], 403);
            }

            $item = $this->em->getRepository(ExampleItem::class)->find($id);

            if (!$item) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Položka nebyla nalezena'
                ], 404);
            }

            $this->em->remove($item);
            $this->em->flush();

            $this->toastSuccess('Položka byla úspěšně smazána');
            return new JsonResponse([
                'success' => true,
                'message' => 'Položka byla úspěšně smazána'
            ]);

        } catch (Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Chyba při mazání položky: ' . $e->getMessage()
            ], 500);
        }
    }
}
