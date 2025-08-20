<?php

namespace Modules\Example\Controllers;

use Doctrine\ORM\Exception\NotSupported;
use Modules\Example\Entities\ExampleItem;
use Core\Render\BaseController;
use Core\Http\Response\ViewResponse;
use Core\Http\Response\JsonResponse;
use Exception;

class ExampleController extends BaseController
{
    /**
     * Zobrazí seznam položek
     */
    public function index(): ViewResponse
    {
        return $this->view('modules.example.index');
    }

    /**
     * Zobrazí formulář pro vytvoření
     */
    public function create(): ViewResponse
    {
        return $this->view('modules.example.create', [
            'title' => 'Nový příklad 3'
        ]);
    }

    /**
     * Uloží novou položku
     */
    public function store(): JsonResponse
    {

        try {
            $data = $this->request->getJson();

            $item = new ExampleItem();
            $item->setName($data['name']);
            $item->setDescription($data['description'] ?? '');
            $item->setStatus($data['status'] ?? 'active');

            $this->em->persist($item);
            $this->em->flush();

            return $this->json([
                'success' => true,
                'message' => 'Položka byla úspěšně vytvořena',
                'data' => [
                    'id' => $item->getId(),
                    'name' => $item->getName()
                ]
            ]);
        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Chyba při vytváření položky: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Zobrazí detail položky
     * @throws NotSupported
     */
    public function show($id): ViewResponse
    {

        $item = $this->em->getRepository(ExampleItem::class)->find($id);
        if (!$item) {
            $this->toastError('Položka nebyla nalezena');
            $this->redirect('/example');
            return $this->view('modules.example.index', ['items' => []]);
        }

        return $this->view('modules.example.show', [
            'item' => $item,
            'title' => 'Detail položky: ' . $item->getName()
        ]);
    }

    /**
     * Zobrazí formulář pro editaci
     * @throws NotSupported
     */
    public function edit($id): ViewResponse
    {

        $item = $this->em->getRepository(ExampleItem::class)->find($id);
        if (!$item) {
            $this->toastError('Položka nebyla nalezena');
            $this->redirect('/example');
            return $this->view('modules.example.index', ['items' => []]);
        }

        return $this->view('modules.example.edit', [
            'item' => $item,
            'title' => 'Editace položky: ' . $item->getName()
        ]);
    }

    /**
     * Aktualizuje položku
     */
    public function update($id): JsonResponse
    {

        try {
            $item = $this->em->getRepository(ExampleItem::class)->find($id);
            if (!$item) {
                return $this->json([
                    'success' => false,
                    'message' => 'Položka nebyla nalezena'
                ], 404);
            }

            $data = $this->request->getJson();

            $item->setName($data['name']);
            $item->setDescription($data['description'] ?? '');
            $item->setStatus($data['status'] ?? 'active');

            $this->em->persist($item);
            $this->em->flush();

            return $this->json([
                'success' => true,
                'message' => 'Položka byla úspěšně aktualizována'
            ]);
        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Chyba při aktualizaci položky: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Smaže položku
     */
    public function delete($id): JsonResponse
    {
        try {
            $item = $this->em->getRepository(ExampleItem::class)->find($id);
            if (!$item) {
                return $this->json([
                    'success' => false,
                    'message' => 'Položka nebyla nalezena'
                ], 404);
            }

            $this->em->remove($item);
            $this->em->flush();

            return $this->json([
                'success' => true,
                'message' => 'Položka byla úspěšně smazána'
            ]);
        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Chyba při mazání položky: ' . $e->getMessage()
            ], 500);
        }
    }
}
