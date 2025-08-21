<?php

declare(strict_types=1);

namespace Core\Services;

use Core\Render\View;

/**
 * Moderní tabulkový komponent s AlpineJS a AJAX funkcionalitou
 *
 * TableUI je pokročilý komponent pro generování interaktivních tabulek s podporou:
 * - AlpineJS pro reaktivní frontend funkcionalité
 * - AJAX komunikace s backendem
 * - Vyhledávání (client-side i server-side)
 * - Řazení dat podle sloupců
 * - Stránkování s dynamickou výškou
 * - Vlastní akce pro řádky
 * - Přizpůsobitelné tlačítka v hlavičce
 * - Hromadné akce s dropdown menu
 * - Responsivní design s Tailwind CSS
 *
 * @package Core\Services
 * @author  Arcadia Development Team
 * @version 2.0.0
 * @since   1.0.0
 *
 * @example
 * ```php
 * // Základní použití
 * $tableUI = new TableUI('customers-table', [
 *     'title' => 'Seznam zákazníků',
 *     'data' => $customers,
 *     'headers' => ['Jméno', 'Email', 'Telefon', 'Status'],
 *     'searchable' => true,
 *     'sortable' => true,
 *     'perPage' => 25
 * ]);
 *
 * // Přidání sloupců
 * $tableUI->addColumn('name', 'Jméno')
 *         ->addColumn('email', 'Email')
 *         ->addColumn('phone', 'Telefon')
 *         ->addColumn('status', 'Status');
 *
 * // Přidání akcí pro řádky
 * $tableUI->addAction('Zobrazit', function($params) {
 *     return "viewCustomer({$params['row']}.id)";
 * }, ['type' => 'primary']);
 *
 * // Přidání vyhledávání
 * $tableUI->addSearchPanel('Vyhledat zákazníka...', function() {
 *     return "searchCustomers()";
 * });
 *
 * // Přidání vlastních tlačítek
 * $tableUI->addButtonToHeader('create', '<i class="fas fa-plus mr-2"></i>Nový', 'pointer', function() {
 *     return "createCustomer()";
 * }, ['type' => 'primary']);
 *
 * // Přidání hromadných akcí
 * $tableUI->addBulkActions([
 *     'delete' => [
 *         'label' => 'Smazat vybrané',
 *         'icon' => 'fas fa-trash',
 *         'type' => 'danger',
 *         'callback' => function($params) {
 *             return "deleteSelected({$params['filteredData']})";
 *         }
 *     ]
 * ]);
 *
 * echo $tableUI->render();
 * ```
 */
class TableUI
{
    /**
     * Konstruktor TableUI komponenty
     *
     * @param string $id Unikátní identifikátor tabulky (používá se pro DOM ID a AlpineJS funkce)
     * @param array $config Konfigurace tabulky
     *
     * Dostupné konfigurační možnosti:
     * - 'title' (string): Název tabulky zobrazený v hlavičce
     * - 'icon' (string): FontAwesome ikona pro hlavičku
     * - 'data' (array): Pole dat pro tabulku (asociativní pole)
     * - 'headers' (array): Názvy sloupců
     * - 'searchable' (bool): Povolit vyhledávání
     * - 'sortable' (bool): Povolit řazení
     * - 'pagination' (bool): Povolit stránkování
     * - 'perPage' (int): Počet záznamů na stránku
     * - 'showActions' (bool): Zobrazit sloupec s akcemi
     * - 'responsive' (bool): Responsivní design
     * - 'loading' (bool): Počáteční loading stav
     * - 'emptyMessage' (string): Zpráva pro prázdnou tabulku
     * - 'ajaxUrl' (string): URL pro AJAX požadavky
     * - 'classes' (array): Přizpůsobení CSS tříd
     *
     * @example
     * ```php
     * $tableUI = new TableUI('products-table', [
     *     'title' => 'Produkty',
     *     'icon' => 'fas fa-box',
     *     'data' => $products,
     *     'headers' => ['Název', 'Cena', 'Kategorie'],
     *     'searchable' => true,
     *     'sortable' => true,
     *     'perPage' => 20
     * ]);
     * ```
     */
    public function __construct(
        private readonly string $id,
        private array $config = []
    ) {
        $this->config = array_merge([
            'headers' => [],
            'data' => [],
            'searchable' => false,
            'sortable' => false,
            'pagination' => true,
            'perPage' => 10,
            'emptyMessage' => 'Žádná data k zobrazení',
            'showActions' => true,
            'responsive' => true,
            'loading' => false,
            'title' => 'Tabulka dat',
            'icon' => 'fas fa-table',
            'ajaxUrl' => '/__render',
            'classes' => [
                'container' => 'bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden',
                'header' => 'bg-gray-50 px-6 py-4 border-b border-gray-200',
                'table' => 'min-w-full divide-y divide-gray-200 table-fixed',
                'thead' => 'bg-gray-50 sticky top-0 z-10 shadow-sm',
                'th' => 'px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50',
                'td' => 'px-6 py-4 whitespace-nowrap text-sm text-gray-900',
                'tr' => 'hover:bg-gray-50 transition-colors',
                'searchPanel' => 'bg-gray-50 px-6 py-4 border-b border-gray-200',
                'searchInput' => 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent',
                'pagination' => 'bg-white px-6 py-3 border-t border-gray-200',
                'emptyState' => 'text-center py-12'
            ]
        ], $config);

        $this->columns = [];
        $this->actions = [];
        $this->searchPanels = [];
        $this->sortableColumns = [];
        $this->searchableColumns = [];
        $this->perPageOptions = [];
        $this->headerButtons = [];
        $this->bulkActions = [];
    }

    private array $columns;
    private array $actions;
    private array $searchPanels;
    private array $sortableColumns;
    private array $searchableColumns;
    private array $perPageOptions;
    private array $headerButtons;
    private array $bulkActions;

    /**
     * Přidá sloupec do tabulky
     *
     * @param string $key Klíč sloupce (odpovídá klíči v datech)
     * @param string $label Zobrazovaný název sloupce
     * @param array $options Volitelné nastavení sloupce
     * @param bool $options['sortable'] Zda je sloupec řaditelný
     * @param string $options['position'] Pozice obsahu sloupce (left|center|right)
     * @param string $options['format'] Formát dat (date|datetime|number|currency|boolean)
     * @param callable $options['convert'] Vlastní konverzní funkce pro data
     * @return self Pro method chaining
     *
     * @example
     * ```php
     * $tableUI->addColumn('name', 'Název zákazníka')
     *         ->addColumn('email', 'E-mail', ['sortable' => true])
     *         ->addColumn('status', 'Stav', ['sortable' => true, 'position' => 'center'])
     *         ->addColumn('price', 'Cena', ['position' => 'right', 'format' => 'currency'])
     *         ->addColumn('created_at', 'Vytvořeno', ['format' => 'date'])
     *         ->addColumn('full_name', 'Celé jméno', [
     *             'convert' => function($row) {
     *                 return $row['first_name'] . ' ' . $row['last_name'];
     *             }
     *         ]);
     * ```
     */
    public function addColumn(string $key, string $label, array $options = []): self
    {
        $this->columns[$key] = [
            'key' => $key,
            'label' => $label,
            'options' => $options
        ];
        return $this;
    }

    /**
     * Přidá akci pro řádky tabulky
     *
     * @param string $label Text tlačítka akce
     * @param callable $callback Funkce vracející JavaScript kód pro onclick handler
     * @param array $options Volitelné nastavení akce (type: default|primary|danger|warning|success)
     * @return self Pro method chaining
     *
     * @example
     * ```php
     * $tableUI->addAction('Zobrazit', function($params) {
     *     return "window.location.href='/customers/' + {$params['row']}.id";
     * }, ['type' => 'primary'])
     * ->addAction('Smazat', function($params) {
     *     return "deleteCustomer({$params['row']}.id)";
     * }, ['type' => 'danger']);
     * ```
     */
    public function addAction(string $label, callable $callback, array $options = []): self
    {
        $this->actions[] = [
            'label' => $label,
            'callback' => $callback,
            'options' => $options
        ];
        return $this;
    }

    /**
     * Přidá panel pro vyhledávání
     *
     * @param string $placeholder Placeholder text pro vyhledávací input
     * @param callable $searchFunction Funkce pro server-side vyhledávání (volitelné)
     * @param array $options Volitelné nastavení panelu
     * @return self Pro method chaining
     *
     * @example
     * ```php
     * $tableUI->addSearchPanel('Vyhledat zákazníka...', function($query) {
     *     return CustomerController::searchCustomers($query);
     * });
     * ```
     */
    public function addSearchPanel(string $placeholder, callable $searchFunction, array $options = []): self
    {
        $this->searchPanels[] = [
            'placeholder' => $placeholder,
            'function' => $searchFunction,
            'options' => $options
        ];
        return $this;
    }

    /**
     * Přidá panel pro výběr počtu záznamů na stránku
     *
     * @param array $options Pole možností pro počet záznamů na stránku
     * @return self Pro method chaining
     *
     * @example
     * ```php
     * $tableUI->addPerPagePanel([10, 25, 50, 100, 200]);
     * ```
     */
    public function addPerPagePanel(array $options = [10, 25, 50, 100]): self
    {
        $this->perPageOptions = $options;
        return $this;
    }

    /**
     * Nastaví řaditelné sloupce
     *
     * @param array $columns Indexy sloupců, které lze řadit
     * @return self Pro method chaining
     *
     * @example
     * ```php
     * $tableUI->setSortableColumns([0, 1, 3]); // Řaditelné jsou sloupce 0, 1 a 3
     * ```
     */
    public function setSortableColumns(array $columns): self
    {
        $this->sortableColumns = $columns;
        return $this;
    }

    /**
     * Nastaví prohledávatelné sloupce
     *
     * @param array $columns Indexy sloupců pro vyhledávání
     * @return self Pro method chaining
     *
     * @example
     * ```php
     * $tableUI->setSearchableColumns([0, 1, 2]); // Vyhledávání ve sloupcích 0, 1 a 2
     * ```
     */
    public function setSearchableColumns(array $columns): self
    {
        $this->searchableColumns = $columns;
        return $this;
    }

    /**
     * Nastaví počet záznamů na stránku
     *
     * @param int $perPage Počet záznamů na stránku
     * @return self Pro method chaining
     */
    public function setPerPage(int $perPage): self
    {
        $this->config['perPage'] = $perPage;
        return $this;
    }

    /**
     * Nastaví zprávu pro prázdnou tabulku
     *
     * @param string $message Text zprávy
     * @return self Pro method chaining
     */
    public function setEmptyMessage(string $message): self
    {
        $this->config['emptyMessage'] = $message;
        return $this;
    }

    /**
     * Nastaví loading stav tabulky
     *
     * @param bool $loading Zda je tabulka v loading stavu
     * @return self Pro method chaining
     */
    public function setLoading(bool $loading): self
    {
        $this->config['loading'] = $loading;
        return $this;
    }

    /**
     * Nastaví název tabulky
     *
     * @param string $title Název tabulky
     * @return self Pro method chaining
     */
    public function setTitle(string $title): self
    {
        $this->config['title'] = $title;
        return $this;
    }

    /**
     * Nastaví ikonu tabulky
     *
     * @param string $icon FontAwesome třída ikony
     * @return self Pro method chaining
     */
    public function setIcon(string $icon): self
    {
        $this->config['icon'] = $icon;
        return $this;
    }

    /**
     * Nastaví URL pro AJAX požadavky
     *
     * @param string $url URL endpointu
     * @return self Pro method chaining
     */
    public function setAjaxUrl(string $url): self
    {
        $this->config['ajaxUrl'] = $url;
        return $this;
    }

    /**
     * Přidá vlastní tlačítko do hlavičky tabulky
     *
     * @param string $id Unikátní ID tlačítka
     * @param string $html HTML obsah tlačítka (text, ikony)
     * @param string $pointer CSS kurzor (obvykle 'pointer')
     * @param callable $action Funkce vracející JavaScript kód pro onclick handler
     * @param array $options Volitelné nastavení (type: default|primary|danger|warning|success)
     * @return self Pro method chaining
     *
     * @example
     * ```php
     * $tableUI->addButtonToHeader(
     *     'create-customer',
     *     '<i class="fas fa-plus mr-2"></i>Nový zákazník',
     *     'pointer',
     *     function($params) {
     *         return "window.location.href='/customers/create'";
     *     },
     *     ['type' => 'primary']
     * )->addButtonToHeader(
     *     'export-csv',
     *     '<i class="fas fa-download mr-2"></i>Export CSV',
     *     'pointer',
     *     function($params) {
     *         return "exportTableData({$params['filteredData']})";
     *     },
     *     ['type' => 'success']
     * );
     * ```
     */
    public function addButtonToHeader(string $id, string $html, string $pointer, callable $action, array $options = []): self
    {
        $this->headerButtons[] = [
            'id' => $id,
            'html' => $html,
            'pointer' => $pointer,
            'action' => $action,
            'options' => $options
        ];
        return $this;
    }

    /**
     * Přidá hromadné akce s dropdown menu
     *
     * @param array $actions Pole akcí ve formátu: ['key' => ['label' => '', 'icon' => '', 'type' => '', 'callback' => callable]]
     * @return self Pro method chaining
     *
     * @example
     * ```php
     * $tableUI->addBulkActions([
     *     'export' => [
     *         'label' => 'Exportovat vybrané',
     *         'icon' => 'fas fa-download',
     *         'type' => 'primary',
     *         'callback' => function($params) {
     *             return "exportSelected({$params['filteredData']})";
     *         }
     *     ],
     *     'delete' => [
     *         'label' => 'Smazat vybrané',
     *         'icon' => 'fas fa-trash',
     *         'type' => 'danger',
     *         'callback' => function($params) {
     *             return "if(confirm('Opravdu smazat?')) deleteSelected({$params['filteredData']})";
     *         }
     *     ],
     *     'activate' => [
     *         'label' => 'Aktivovat vybrané',
     *         'icon' => 'fas fa-check-circle',
     *         'type' => 'success',
     *         'callback' => function($params) {
     *             return "activateSelected({$params['filteredData']})";
     *         }
     *     ]
     * ]);
     * ```
     */
    public function addBulkActions(array $actions): self
    {
        $this->bulkActions = $actions;
        return $this;
    }

    /**
     * Vyrenderuje kompletní HTML tabulky
     *
     * @return string Kompletní HTML kód tabulky s AlpineJS funkcionalitou
     *
     * @example
     * ```php
     * $tableUI = new TableUI('users-table', $config);
     * // ... konfigurace sloupců, akcí, atd.
     * echo $tableUI->render();
     * ```
     */
    public function render(): string
    {
        $html = $this->renderContainer();
        $html .= '<div x-show="initialized">';
        $html .= $this->renderHeader();
        $html .= $this->renderSearchPanels();
        $html .= $this->renderTable();
        $html .= $this->renderPagination();
        $html .= '</div>';
        $html .= $this->renderAlpineScript();
        $html .= '</div>';

        return $html;
    }

    private function renderContainer(): string
    {
        $loadingClass = $this->config['loading'] ? ' opacity-50 pointer-events-none' : '';
        $functionName = str_replace(['-', ' '], '_', $this->id);
        return sprintf(
            '<div id="table-container-%s" class="%s%s" x-data="tableUI_%s()" x-cloak>
                <!-- Initial loading state -->
                <div x-show="!initialized" class="flex items-center justify-center py-12">
                    <div class="flex items-center space-x-3">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                        <span class="text-lg text-gray-600">Načítání tabulky...</span>
                    </div>
                </div>
            ',
            $this->id,
            $this->config['classes']['container'],
            $loadingClass,
            $functionName
        );
    }

    private function renderHeader(): string
    {
        $data = $this->config['data'];
        $totalRecords = count($data);
        $perPage = $this->config['perPage'];
        $visibleRecords = min($perPage, $totalRecords);
        $title = $this->config['title'];
        $icon = $this->config['icon'];

        return <<<HTML
        <div class="{$this->config['classes']['header']}">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center shadow-sm">
                            <i class="{$icon} text-white text-sm"></i>
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center space-x-3">
                            <h2 class="text-lg font-semibold text-gray-900">{$title}</h2>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                <i class="fas fa-list mr-1"></i><span x-text="totalRecords"></span> záznamů
                            </span>
                        </div>
                        <div class="flex items-center space-x-4 mt-1">
                            <p class="text-sm text-gray-600">
                                <i class="fas fa-info-circle mr-1"></i>Zobrazeno <span x-text="visibleRecords"></span> z <span x-text="totalRecords"></span> záznamů
                            </p>
                        </div>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    {$this->renderHeaderActions()}
                </div>
            </div>
        </div>
        HTML;
    }

    private function renderHeaderActions(): string
    {
        $actions = [];

        if ($this->config['searchable']) {
            $actions[] = '<button @click="toggleSearch()" class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors cursor-pointer search-toggle-btn">
                <i class="fas fa-search mr-2"></i>Vyhledat
            </button>';
        }

        // Přidání vlastních tlačítek (za Vyhledat, před Resetovat řazení)
        foreach ($this->headerButtons as $button) {
            $type = $button['options']['type'] ?? 'default';
            $colorClass = match($type) {
                'danger' => 'bg-red-100 text-red-800 hover:bg-red-200 border-red-300',
                'warning' => 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200 border-yellow-300',
                'success' => 'bg-green-100 text-green-800 hover:bg-green-200 border-green-300',
                'primary' => 'bg-blue-100 text-blue-800 hover:bg-blue-200 border-blue-300',
                default => 'bg-white text-gray-700 hover:bg-gray-50 border-gray-300'
            };

            $callback = $button['action'];
            $onclick = $callback(['table' => 'this', 'data' => 'this.data', 'filteredData' => 'this.filteredData']);

            $actions[] = sprintf(
                '<button id="%s" @click="%s" class="inline-flex items-center px-3 py-2 text-sm font-medium border rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors %s">
                    %s
                </button>',
                $button['id'],
                $onclick,
                $colorClass,
                $button['html']
            );
        }

        if ($this->config['sortable']) {
            $actions[] = '<button @click="resetSort()" class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors cursor-pointer">
                <i class="fas fa-sort mr-2"></i>Resetovat řazení
            </button>';
        }

        if (!empty($this->perPageOptions)) {
            $options = [];
            foreach ($this->perPageOptions as $option) {
                $options[] = sprintf('<option value="%d">%d</option>', $option, $option);
            }

            $actions[] = sprintf(
                '<div class="inline-flex items-center space-x-2">
                    <label class="text-sm font-medium text-gray-700">Záznamů:</label>
                    <div class="relative">
                        <select x-model="perPage" @change="changePerPage()" class="appearance-none px-3 py-2 pr-8 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 focus:border-transparent transition-colors cursor-pointer" style="-webkit-appearance: none; -moz-appearance: none; background-image: none;">
                            %s
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </div>
                </div>',
                implode('', $options)
            );
        }

        // Přidání bulk actions dropdown
        if (!empty($this->bulkActions)) {
            $actions[] = $this->renderBulkActionsDropdown();
        }

        return implode('', $actions);
    }

    private function renderSearchPanels(): string
    {
        if (empty($this->searchPanels)) {
            return '';
        }

        $panels = [];
        foreach ($this->searchPanels as $index => $panel) {
            $panels[] = sprintf(
                '<div class="mb-3">
                    <input type="text"
                           x-model="searchTerms[%d]"
                           @input.debounce.300ms="search()"
                           class="%s"
                           placeholder="%s">
                </div>',
                $index,
                $this->config['classes']['searchInput'],
                htmlspecialchars($panel['placeholder'])
            );
        }

        return sprintf(
            '<div x-show="showSearch" x-transition x-cloak class="%s">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="text-sm font-medium text-gray-700">Vyhledávání</h4>
                    <button @click="toggleSearch()" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div>
                    %s
                </div>
            </div>',
            $this->config['classes']['searchPanel'],
            implode('', $panels)
        );
    }

        private function renderTable(): string
    {
        $html = '<div class="relative" x-cloak>';

        // Loading overlay
        $html .= '<div x-show="loading" x-transition class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center z-20">
            <div class="flex items-center space-x-2">
                <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                <span class="text-sm text-gray-600">Načítání...</span>
            </div>
        </div>';

        // Table wrapper with dynamic height
        $html .= '<div class="border border-gray-200 rounded-lg overflow-hidden" style="min-height: 400px;">';
        $html .= '<div class="overflow-auto" style="max-height: 600px;">';
        $html .= sprintf('<table id="%s" class="%s">', $this->id, $this->config['classes']['table']);

        // Header
        $html .= $this->renderTableHeader();

        // Body
        $html .= $this->renderTableBody();

        $html .= '</table>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    private function renderTableHeader(): string
    {
        $headers = $this->config['headers'];
        if (empty($headers) && !empty($this->columns)) {
            $headers = array_map(fn($col) => $col['label'], $this->columns);
        }

        $html = sprintf('<thead class="%s">', $this->config['classes']['thead']);
        $html .= '<tr>';

        foreach ($headers as $index => $header) {
            $sortable = in_array($index, $this->sortableColumns);
            $sortClass = $sortable ? ' sortable cursor-pointer' : '';
            $clickHandler = $sortable ? '@click="sort(' . $index . ')"' : '';

            // Získat pozici sloupce z definovaných sloupců
            $position = 'left'; // výchozí pozice
            if (!empty($this->columns)) {
                $columnKeys = array_keys($this->columns);
                if (isset($columnKeys[$index])) {
                    $columnKey = $columnKeys[$index];
                    if (isset($this->columns[$columnKey]['options']['position'])) {
                        $position = $this->columns[$columnKey]['options']['position'];
                    }
                }
            }

            // CSS třídy pro pozici
            $positionClass = match($position) {
                'center' => 'text-center',
                'right' => 'text-right',
                default => 'text-left'
            };

            $sortIcon = '';
            if ($sortable) {
                $sortIcon = sprintf('
                    <div class="flex flex-col space-y-0.5 ml-2">
                        <svg class="w-3 h-3 transition-all duration-200"
                             :class="sortColumn === %d && sortDirection === \'asc\' ? \'text-blue-500 opacity-100\' : \'text-gray-400 opacity-50\'"
                             fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"/>
                        </svg>
                        <svg class="w-3 h-3 transition-all duration-200"
                             :class="sortColumn === %d && sortDirection === \'desc\' ? \'text-blue-500 opacity-100\' : \'text-gray-400 opacity-50\'"
                             fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                ', $index, $index);
            }

            $html .= sprintf(
                '<th class="%s%s %s" %s>
                    <div class="flex items-center">
                        <span>%s</span>
                        %s
                    </div>
                </th>',
                $this->config['classes']['th'],
                $sortClass,
                $positionClass,
                $clickHandler,
                htmlspecialchars($header),
                $sortIcon
            );
        }

        if ($this->config['showActions'] && !empty($this->actions)) {
            $html .= sprintf('<th class="%s w-32">Akce</th>', $this->config['classes']['th']);
        }

        $html .= '</tr>';
        $html .= '</thead>';

        return $html;
    }

    private function renderTableBody(): string
    {
        $html = '<tbody class="bg-white divide-y divide-gray-200" x-cloak>';
        $html .= '<template x-for="(row, rowIndex) in paginatedData" :key="rowIndex">';
        $html .= '<tr class="' . $this->config['classes']['tr'] . '">';

        // Data cells - pro asociativní pole
        $headers = $this->config['headers'];

        // Použít definované sloupce nebo dynamicky získat klíče
        $dataKeys = [];
        if (!empty($this->columns)) {
            // Použít definované sloupce
            $dataKeys = array_keys($this->columns);
        } elseif (!empty($this->config['data'])) {
            // Dynamicky získat klíče z prvního řádku dat
            $firstRow = $this->config['data'][0];
            $dataKeys = array_keys($firstRow);
        }

        foreach ($dataKeys as $index => $key) {
            if ($index < count($headers)) {
                // Získat pozici sloupce
                $position = 'left'; // výchozí pozice
                $format = null;
                $convert = null;

                if (isset($this->columns[$key]['options'])) {
                    $options = $this->columns[$key]['options'];
                    $position = $options['position'] ?? 'left';
                    $format = $options['format'] ?? null;
                    $convert = $options['convert'] ?? null;
                }

                // CSS třídy pro pozici
                $positionClass = match($position) {
                    'center' => 'text-center',
                    'right' => 'text-right',
                    default => 'text-left'
                };

                // Generovat obsah buňky podle formátu
                if ($format) {
                    // Předdefinované formáty - použijeme AlpineJS
                    $html .= sprintf(
                        '<td class="%s %s" x-text="formatCell(row[\'%s\'], \'%s\')"></td>',
                        $this->config['classes']['td'],
                        $positionClass,
                        $key,
                        $format
                    );
                } else {
                    // Standardní zobrazení (včetně konvertovaných hodnot)
                    $html .= sprintf(
                        '<td class="%s %s" x-html="row[\'%s\']"></td>',
                        $this->config['classes']['td'],
                        $positionClass,
                        $key
                    );
                }
            }
        }

        // Actions
        if ($this->config['showActions'] && !empty($this->actions)) {
            $html .= $this->renderRowActions();
        }

        $html .= '</tr>';
        $html .= '</template>';

        // Empty state
        $html .= '<tr x-show="paginatedData.length === 0" x-cloak>';
        $colspan = count($this->config['headers']) + ($this->config['showActions'] && !empty($this->actions) ? 1 : 0);
        $html .= sprintf(
            '<td colspan="%d" class="%s">%s</td>',
            $colspan,
            $this->config['classes']['emptyState'],
            $this->config['emptyMessage']
        );
        $html .= '</tr>';

        $html .= '</tbody>';
        return $html;
    }

    private function renderRowActions(): string
    {
        $html = sprintf('<td class="%s">', $this->config['classes']['td']);
        $html .= '<div class="flex items-center space-x-2">';

        foreach ($this->actions as $action) {
            $type = $action['options']['type'] ?? 'button';
            $colorClass = match($type) {
                'danger' => 'bg-red-100 text-red-800 hover:bg-red-200',
                'warning' => 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200',
                'success' => 'bg-green-100 text-green-800 hover:bg-green-200',
                default => 'bg-blue-100 text-blue-800 hover:bg-blue-200'
            };

            $callback = $action['callback'];
            $onclick = $callback(['row' => 'row', 'index' => 'rowIndex']);

            $html .= sprintf(
                '<button class="px-3 py-1 text-xs font-medium rounded-md %s transition-colors" @click="%s">
                    %s
                </button>',
                $colorClass,
                $onclick,
                htmlspecialchars($action['label'])
            );
        }

        $html .= '</div>';
        $html .= '</td>';

        return $html;
    }

    private function renderPagination(): string
    {
        if (!$this->config['pagination']) {
            return '';
        }

        return sprintf(
            '<div class="%s" x-cloak>
                <div class="flex items-center justify-between">
                    <div class="flex items-center text-sm text-gray-700">
                        <span>Zobrazeno <span x-text="visibleRecords"></span> z <span x-text="totalRecords"></span> záznamů</span>
                    </div>
                    <div class="flex items-center space-x-2" x-show="totalPages > 1">
                        <button @click="goToPage(1)" class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50 transition-colors cursor-pointer">První</button>
                        <button @click="goToPage(currentPage - 1)" :disabled="currentPage <= 1" class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50 transition-colors cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">Předchozí</button>
                        <span class="px-3 py-1 text-sm">Stránka <span x-text="currentPage"></span> z <span x-text="totalPages"></span></span>
                        <button @click="goToPage(currentPage + 1)" :disabled="currentPage >= totalPages" class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50 transition-colors cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">Další</button>
                        <button @click="goToPage(totalPages)" class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50 transition-colors cursor-pointer">Poslední</button>
                    </div>
                </div>
            </div>',
            $this->config['classes']['pagination']
        );
    }

    private function getColumnKeysForJS(): string
    {
        if (!empty($this->columns)) {
            $keys = array_keys($this->columns);
            return json_encode($keys);
        }
        return '[]';
    }

    private function getHeadersForJS(): string
    {
        return json_encode($this->config['headers']);
    }

    private function hasSearchPanels(): string
    {
        return !empty($this->searchPanels) ? 'true' : 'false';
    }

    private function getSearchControllerInfo(): string
    {
        // Získáme informace o controlleru z konfigurace nebo použijeme výchozí
        $controller = $this->config['search_controller'] ?? 'App\\\\Controllers\\\\CustomerController';
        $method = $this->config['search_method'] ?? 'ajaxSearch';

        return json_encode([
            'controller' => $controller,
            'method' => $method
        ]);
    }

    private function renderAlpineScript(): string
    {
        // Aplikovat konverzní funkce na data před předáním do AlpineJS
        $processedData = $this->applyConvertFunctions($this->config['data']);
        $initialData = json_encode($processedData);
        $ajaxUrl = $this->config['ajaxUrl'];
        $tableId = $this->id;
        $functionName = str_replace(['-', ' '], '_', $this->id);

        $html =  <<<BLADE
        <script>
        function tableUI_{$functionName}() {
            return {
                data: {$initialData},
                filteredData: {$initialData},
                paginatedData: [],
                searchTerms: {},
                sortColumn: null,
                sortDirection: 'asc',
                currentPage: 1,
                perPage: {$this->config['perPage']},
                showSearch: false,

                loading: false,
                initialized: false,

                // Computed properties
                get totalRecords() {
                    return this.data.length;
                },
                get totalPages() {
                    return Math.ceil(this.filteredData.length / this.perPage);
                },
                get visibleRecords() {
                    return this.paginatedData.length;
                },

                // Methods
                init() {
                    // Inicializace filteredData
                    this.filteredData = [...this.data];

                    this.calculateDynamicHeight();
                    this.updatePagination();

                    // Poslouchač na změnu velikosti okna
                    window.addEventListener('resize', () => {
                        this.calculateDynamicHeight();
                    });

                    // Označit jako inicializované
                    this.initialized = true;
                },

                calculateDynamicHeight() {
                    // Výška viewport minus header, search panel, pagination a rezerva
                    const availableHeight = window.innerHeight - 300; // 300px rezerva pro header atd.
                    const headerHeight = 60; // Výška header tabulky

                    const tableBodyHeight = availableHeight - headerHeight;
                    const maxHeight = Math.max(400, tableBodyHeight); // Minimálně 400px

                    // Nastavit maximální výšku tabulky
                    const tableWrapper = document.querySelector('#{$this->id}').closest('.overflow-auto');
                    if (tableWrapper) {
                        tableWrapper.style.maxHeight = maxHeight + 'px';
                    }
                },

                async callAjax(callFunction, params = {}) {
                    try {
                        this.loading = true;

                        const response = await fetch('{$ajaxUrl}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({
                                call_function: callFunction,
                                params: {
                                    table_id: '{$tableId}',
                                    ...params
                                }
                            })
                        });

                        const result = await response.json();

                        if (result.success) {
                            return result;
                        } else {
                            return null;
                        }
                    } catch (error) {
                        return null;
                    } finally {
                        this.loading = false;
                    }
                },

                async search() {
                    const searchTerm = Object.values(this.searchTerms).join(' ').toLowerCase();

                    if (!searchTerm) {
                        this.filteredData = this.data;
                    } else {
                        // Pokud jsou definované search panely, použijeme AJAX
                        if ({$this->hasSearchPanels()}) {
                            const searchControllerInfo = {$this->getSearchControllerInfo()};
                            const result = await this.callAjax('controller_method', {
                                controller: searchControllerInfo.controller,
                                method: searchControllerInfo.method,
                                params: [searchTerm]
                            });

                            if (result && result.success) {
                                this.filteredData = result.data.data || [];
                            } else {
                                this.filteredData = [];
                            }
                        } else {
                            // Fallback na client-side search
                            this.filteredData = this.data.filter(row => {
                                const rowValues = Object.values(row);
                                return rowValues.some(value => {
                                    const stringValue = String(value).toLowerCase();
                                    return stringValue.includes(searchTerm);
                                });
                            });
                        }
                    }

                    this.currentPage = 1;
                    this.updatePagination();
                },

                sort(column) {
                    if (this.sortColumn === column) {
                        this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
                    } else {
                        this.sortColumn = column;
                        this.sortDirection = 'asc';
                    }

                    // Použít definované sloupce nebo dynamicky získat klíče
                    let columnKeys = [];
                    if (this.data.length > 0) {
                        // Pokud máme definované sloupce, použijeme je
                        const definedColumns = {$this->getColumnKeysForJS()};
                        if (definedColumns.length > 0) {
                            columnKeys = definedColumns;
                        } else {
                            // Jinak dynamicky získáme klíče
                            columnKeys = Object.keys(this.data[0]);
                        }
                    }
                    const key = columnKeys[column];

                    // Client-side sorting
                    this.performClientSort(key);
                    this.updatePagination();
                },

                performClientSort(key) {
                    this.filteredData.sort((a, b) => {
                        let aValue = a[key] || '';
                        let bValue = b[key] || '';

                        // Konvertovat na string pro porovnání
                        aValue = String(aValue).toLowerCase();
                        bValue = String(bValue).toLowerCase();

                        let result = 0;

                        // Speciální logika pro čísla
                        if (!isNaN(aValue) && !isNaN(bValue)) {
                            result = parseFloat(aValue) - parseFloat(bValue);
                        } else {
                            // String porovnání s locale support
                            result = aValue.localeCompare(bValue, 'cs');
                        }

                        // Obrátit výsledek pro desc řazení
                        return this.sortDirection === 'desc' ? -result : result;
                    });
                },

                resetSort() {
                    this.sortColumn = null;
                    this.sortDirection = 'asc';
                    this.filteredData = [...this.data];
                    this.updatePagination();
                },

                updatePagination() {
                    const startIndex = (this.currentPage - 1) * this.perPage;
                    const endIndex = startIndex + this.perPage;
                    this.paginatedData = this.filteredData.slice(startIndex, endIndex);
                },

                goToPage(page) {
                    if (page >= 1 && page <= this.totalPages) {
                        this.currentPage = page;
                        this.updatePagination();
                    }
                },

                changePerPage() {
                    this.currentPage = 1;
                    this.updatePagination();
                },

                toggleSearch() {
                    this.showSearch = !this.showSearch;
                    if (!this.showSearch) {
                        this.searchTerms = {};
                        this.search();
                    }
                },

                formatCell(value, format) {
                    if (!value) return '';

                    switch (format) {
                        case 'date':
                            return new Date(value).toLocaleDateString('cs-CZ');
                        case 'datetime':
                            return new Date(value).toLocaleString('cs-CZ');
                        case 'number':
                            return new Intl.NumberFormat('cs-CZ').format(value);
                        case 'currency':
                            return new Intl.NumberFormat('cs-CZ', {
                                style: 'currency',
                                currency: 'CZK'
                            }).format(value);
                        case 'boolean':
                            return value ? 'Ano' : 'Ne';
                        default:
                            return value;
                    }
                },

                getConvertedValue(row, key) {
                    // Pro konverzní funkce bychom potřebovali předat PHP funkce do JS
                    // Pro jednoduchost zatím vracíme původní hodnotu
                    // V reálné implementaci by se zde aplikovaly konverzní funkce
                    return row[key] || '';
                },


            }
        }
        </script>
        BLADE;

        return View::html($html, ['initialData' => $initialData, 'functionName' => $functionName]);
    }

    /**
     * Aplikuje konverzní funkce na data
     *
     * @param array $data Původní data
     * @return array Data s aplikovanými konverzními funkcemi
     */
    private function applyConvertFunctions(array $data): array
    {
        if (empty($this->columns)) {
            return $data;
        }

        $processedData = [];
        foreach ($data as $row) {
            $processedRow = $row;
            foreach ($this->columns as $key => $column) {
                if (isset($column['options']['convert']) && is_callable($column['options']['convert'])) {
                    $convertFunction = $column['options']['convert'];
                    $processedRow[$key] = $convertFunction($row);
                }
            }
            $processedData[] = $processedRow;
        }

        return $processedData;
    }

    /**
     * Renderuje buňku s konverzní funkcí
     *
     * @param callable $convert Konverzní funkce
     * @param string $key Klíč sloupce
     * @return string HTML obsah buňky
     */
    private function renderConvertedCell(callable $convert, string $key): string
    {
        // Pro konverzní funkce použijeme AlpineJS s dynamickým obsahem
        // Konverzní funkce se aplikují na data před renderováním
        return sprintf(
            '<span x-text="getConvertedValue(row, \'%s\')"></span>',
            $key
        );
    }

    private function renderBulkActionsDropdown(): string
    {
        $actionsHtml = '';
        foreach ($this->bulkActions as $key => $action) {
            $type = $action['type'] ?? 'default';
            $colorClass = match($type) {
                'danger' => 'text-red-700 hover:bg-red-50',
                'warning' => 'text-yellow-700 hover:bg-yellow-50',
                'success' => 'text-green-700 hover:bg-green-50',
                'primary' => 'text-blue-700 hover:bg-blue-50',
                default => 'text-gray-700 hover:bg-gray-50'
            };

            $callback = $action['callback'];
            $onclick = $callback(['table' => 'this', 'data' => 'this.data', 'filteredData' => 'this.filteredData']);

            $actionsHtml .= sprintf(
                '<button @click="%s; showBulkActions = false" class="w-full text-left px-4 py-2 text-sm %s transition-colors">
                    <i class="%s mr-2"></i>%s
                </button>',
                $onclick,
                $colorClass,
                $action['icon'] ?? 'fas fa-cog',
                htmlspecialchars($action['label'])
            );
        }

        return sprintf(
            '<div class="relative" x-data="{ showBulkActions: false }" x-cloak>
                <button @click="showBulkActions = !showBulkActions" class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors cursor-pointer">
                    <i class="fas fa-cogs mr-2"></i>Hromadné akce
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div x-show="showBulkActions" @click.away="showBulkActions = false" x-transition x-cloak class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg border border-gray-200 z-50">
                    <div class="py-1">
                        %s
                    </div>
                </div>
            </div>',
            $actionsHtml
        );
    }
}
