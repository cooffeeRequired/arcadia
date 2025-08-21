<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Http\Request;
use Core\Http\Response\JsonResponse;
use Core\Http\Response\HtmlResponse;
use Core\Services\TableUI;

class AjaxController
{
    public function render(): JsonResponse
    {
        $request = \Core\Http\Request::getInstance();
        $data = $request->getJson();

        if (!isset($data['call_function'])) {
            return JsonResponse::error('Missing call_function parameter', 400);
        }

        $callFunction = $data['call_function'];
        $params = $data['params'] ?? [];

        try {
            switch ($callFunction) {
                case 'table_render':
                    return $this->renderTable($params);
                case 'table_search':
                    return $this->searchTable($params);
                case 'table_sort':
                    return $this->sortTable($params);
                case 'table_pagination':
                    return $this->paginateTable($params);
                case 'table_per_page':
                    return $this->changePerPage($params);
                case 'controller_method':
                    return $this->callControllerMethod($params);
                default:
                    return JsonResponse::error('Unknown function: ' . $callFunction, 400);
            }
        } catch (\Exception $e) {
            return JsonResponse::error($e->getMessage(), 500);
        }
    }

    private function renderTable(array $params): JsonResponse
    {
        $tableId = $params['table_id'] ?? 'default';
        $config = $params['config'] ?? [];

        // Vytvoření instance TableUI s novým syntaxem
        $table = new TableUI($tableId, $config);

        // Přidání sloupců pomocí fluent interface
        if (isset($params['columns'])) {
            foreach ($params['columns'] as $column) {
                $table->addColumn($column['key'], $column['label'], $column['options'] ?? []);
            }
        }

        // Přidání akcí pomocí fluent interface
        if (isset($params['actions'])) {
            foreach ($params['actions'] as $action) {
                $table->addAction($action['label'], $action['callback'], $action['options'] ?? []);
            }
        }

        // Přidání vyhledávacích panelů
        if (isset($params['search_panels'])) {
            foreach ($params['search_panels'] as $searchPanel) {
                $table->addSearchPanel(
                    $searchPanel['placeholder'],
                    $searchPanel['function'],
                    $searchPanel['options'] ?? []
                );
            }
        }

        // Nastavení dalších vlastností pomocí fluent interface
        if (isset($params['sortable_columns'])) {
            $table->setSortableColumns($params['sortable_columns']);
        }

        if (isset($params['searchable_columns'])) {
            $table->setSearchableColumns($params['searchable_columns']);
        }

        if (isset($params['per_page_options'])) {
            $table->addPerPagePanel($params['per_page_options']);
        }

        if (isset($params['per_page'])) {
            $table->setPerPage($params['per_page']);
        }

        if (isset($params['empty_message'])) {
            $table->setEmptyMessage($params['empty_message']);
        }

        if (isset($params['title'])) {
            $table->setTitle($params['title']);
        }

        if (isset($params['icon'])) {
            $table->setIcon($params['icon']);
        }

        if (isset($params['ajax_url'])) {
            $table->setAjaxUrl($params['ajax_url']);
        }

        $html = $table->render();

        return JsonResponse::success([
            'html' => $html,
            'table_id' => $tableId
        ], 'Table rendered successfully');
    }

    private function searchTable(array $params): JsonResponse
    {
        $tableId = $params['table_id'] ?? '';
        $searchTerm = $params['search_term'] ?? '';
        $data = $params['data'] ?? [];

        if (empty($searchTerm)) {
            return JsonResponse::success(['data' => $data], 'Search completed');
        }

        // Zkusit získat originální data ze session podle table ID
        $session = session();
        $sessionKey = 'table_search_' . $tableId;
        $originalData = $session->get($sessionKey, $data);

        // Použít search logiku podle table ID
        $filteredData = match($tableId) {
            'customers' => $this->searchCustomers($originalData, $searchTerm),
            default => $this->defaultSearch($data, $searchTerm)
        };

        return JsonResponse::success(['data' => array_values($filteredData)], 'Search completed');
    }

    private function searchCustomers(array $data, string $searchTerm): array
    {
        return array_filter($data, function($row) use ($searchTerm) {
            return str_contains(strtolower($row['name']), strtolower($searchTerm)) ||
                str_contains(strtolower($row['email']), strtolower($searchTerm)) ||
                str_contains(strtolower($row['company']), strtolower($searchTerm));
        });
    }

    private function defaultSearch(array $data, string $searchTerm): array
    {
        return array_filter($data, function($row) use ($searchTerm) {
            foreach ($row as $cell) {
                if (stripos((string)$cell, $searchTerm) !== false) {
                    return true;
                }
            }
            return false;
        });
    }

    private function sortTable(array $params): JsonResponse
    {
        $data = $params['data'] ?? [];
        $column = $params['column'] ?? 0;
        $direction = $params['direction'] ?? 'asc';

        usort($data, function($a, $b) use ($column, $direction) {
            $aValue = $a[$column] ?? '';
            $bValue = $b[$column] ?? '';

            if ($direction === 'asc') {
                return strcasecmp((string)$aValue, (string)$bValue);
            } else {
                return strcasecmp((string)$bValue, (string)$aValue);
            }
        });

        return JsonResponse::success($data, 'Table sorted successfully');
    }

    private function paginateTable(array $params): JsonResponse
    {
        $data = $params['data'] ?? [];
        $page = $params['page'] ?? 1;
        $perPage = $params['per_page'] ?? 10;

        $totalRecords = count($data);
        $totalPages = (int) ceil($totalRecords / $perPage);
        $startIndex = ($page - 1) * $perPage;
        $endIndex = min($startIndex + $perPage, $totalRecords);

        $paginatedData = array_slice($data, $startIndex, $perPage);

        return JsonResponse::success([
            'data' => $paginatedData,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_records' => $totalRecords,
                'per_page' => $perPage,
                'start_index' => $startIndex,
                'end_index' => $endIndex
            ]
        ], 'Pagination completed');
    }

    private function changePerPage(array $params): JsonResponse
    {
        $perPage = $params['per_page'] ?? 10;

        return JsonResponse::success(['per_page' => $perPage], 'Per page changed successfully');
    }

    private function callControllerMethod(array $params): JsonResponse
    {
        $controllerClass = $params['controller'] ?? '';
        $method = $params['method'] ?? '';
        $methodParams = $params['params'] ?? [];

        if (empty($controllerClass) || empty($method)) {
            return JsonResponse::error('Missing controller or method parameter', 400);
        }

        try {
            // Vytvoření instance controlleru
            $controller = new $controllerClass();

            // Volání metody s parametry
            $result = call_user_func_array([$controller, $method], $methodParams);

            return JsonResponse::success($result, 'Method called successfully');
        } catch (\Exception $e) {
            return JsonResponse::error('Error calling controller method: ' . $e->getMessage(), 500);
        }
    }
}
