<?php

declare(strict_types=1);

namespace Core\Services;

use Core\Render\View;

/**
 * Moderní header komponent s AlpineJS funkcionalitou
 *
 * HeaderUI je pokročilý komponent pro generování interaktivních headerů s podporou:
 * - AlpineJS pro reaktivní frontend funkcionalité
 * - Přizpůsobitelné ikony a tituly
 * - Podtitulky s obrázky
 * - Vlastní tlačítka s různými styly
 * - Statistiky a metriky
 * - Responsivní design s Tailwind CSS
 *
 * @package Core\Services
 * @author  Arcadia Development Team
 * @version 1.0.0
 * @since   1.0.0
 *
 * @example
 * ```php
 * // Základní použití
 * $headerUI = new HeaderUI('customers-header', [
 *     'title' => 'Seznam zákazníků',
 *     'icon' => 'fas fa-users',
 *     'subtitle' => 'Správa zákazníků a kontaktů'
 * ]);
 *
 * // Přidání podtitulku s obrázkem
 * $headerUI->setSub([
 *     'image' => '/images/customers-icon.png',
 *     'text' => 'Celkem 50 zákazníků'
 * ]);
 *
 * // Přidání tlačítek
 * $headerUI->addButton('create', '<i class="fas fa-plus mr-2"></i>Nový zákazník', function() {
 *     return "window.location.href='/customers/create'";
 * }, ['type' => 'primary']);
 *
 * echo $headerUI->render();
 * ```
 */
class HeaderUI
{
    /**
     * Konstruktor HeaderUI komponenty
     *
     * @param string $id Unikátní identifikátor headeru
     * @param array $config Konfigurace headeru
     *
     * Dostupné konfigurační možnosti:
     * - 'title' (string): Hlavní název headeru
     * - 'icon' (string): FontAwesome ikona pro header
     * - 'subtitle' (string): Podtitulek headeru
     * - 'stats' (array): Statistiky pro zobrazení
     * - 'lastUpdate' (string): Text poslední aktualizace
     * - 'classes' (array): Přizpůsobení CSS tříd
     *
     * @example
     * ```php
     * $headerUI = new HeaderUI('products-header', [
     *     'title' => 'Produkty',
     *     'icon' => 'fas fa-box',
     *     'subtitle' => 'Správa produktového katalogu',
     *     'lastUpdate' => 'Poslední aktualizace: ' . date('d.m.Y H:i')
     * ]);
     * ```
     */
    public function __construct(
        private readonly string $id,
        private array $config = []
    ) {
        $this->config = array_merge([
            'title' => 'Header',
            'icon' => 'fas fa-header',
            'subtitle' => '',
            'stats' => [],
            'lastUpdate' => '',
            'classes' => [
                'container' => 'bg-white rounded-lg shadow-sm border border-gray-200 mb-8',
                'header' => 'px-6 py-4 border-b border-gray-200',
                'icon' => 'w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center shadow-sm',
                'title' => 'text-2xl font-bold text-gray-900',
                'subtitle' => 'text-sm text-gray-600',
                'stats' => 'px-6 py-3 bg-gray-50 rounded-b-lg',
                'button' => 'inline-flex items-center px-4 py-2 text-sm font-medium border border-transparent rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 transition-all duration-200 shadow-sm cursor-pointer'
            ]
        ], $config);

        $this->buttons = [];
        $this->subs = [];
    }

    private array $buttons;
    private array $subs;

    /**
     * Nastaví ikonu headeru
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
     * Nastaví titul headeru
     *
     * @param string $title Hlavní název headeru
     * @return self Pro method chaining
     */
    public function setTitle(string $title): self
    {
        $this->config['title'] = $title;
        return $this;
    }

    /**
     * Nastaví podtitulek headeru
     *
     * @param string $subtitle Podtitulek headeru
     * @return self Pro method chaining
     */
    public function setSubtitle(string $subtitle): self
    {
        $this->config['subtitle'] = $subtitle;
        return $this;
    }

    /**
     * Přidá podtitulek s obrázkem
     *
     * @param array $sub Konfigurace podtitulku ['image' => '', 'text' => '', 'type' => '']
     * @return self Pro method chaining
     *
     * @example
     * ```php
     * $headerUI->setSub([
     *     'image' => '/images/customers-icon.png',
     *     'text' => 'Celkem 50 zákazníků',
     *     'type' => 'success'
     * ]);
     * ```
     */
    public function setSub(array $sub): self
    {
        $this->subs[] = $sub;
        return $this;
    }

    /**
     * Přidá tlačítko do headeru
     *
     * @param string $id Unikátní ID tlačítka
     * @param string $html HTML obsah tlačítka (text, ikony)
     * @param callable $action Funkce vracející JavaScript kód pro onclick handler
     * @param array $options Volitelné nastavení (type: default|primary|danger|warning|success)
     * @return self Pro method chaining
     *
     * @example
     * ```php
     * $headerUI->addButton(
     *     'create-customer',
     *     '<i class="fas fa-plus mr-2"></i>Nový zákazník',
     *     function() {
     *         return "window.location.href='/customers/create'";
     *     },
     *     ['type' => 'primary']
     * );
     * ```
     */
    public function addButton(string $id, string $html, callable $action, array $options = []): self
    {
        $this->buttons[] = [
            'id' => $id,
            'html' => $html,
            'action' => $action,
            'options' => $options
        ];
        return $this;
    }

    /**
     * Nastaví statistiky pro zobrazení
     *
     * @param array $stats Pole statistik ve formátu ['label' => 'count']
     * @return self Pro method chaining
     *
     * @example
     * ```php
     * $headerUI->setStats([
     *     'total' => ['label' => 'Celkem', 'count' => 50, 'type' => 'blue'],
     *     'companies' => ['label' => 'Společnosti', 'count' => 33, 'type' => 'green'],
     *     'persons' => ['label' => 'Osoby', 'count' => 17, 'type' => 'yellow']
     * ]);
     * ```
     */
    public function setStats(array $stats): self
    {
        $this->config['stats'] = $stats;
        return $this;
    }

    /**
     * Nastaví text poslední aktualizace
     *
     * @param string $lastUpdate Text poslední aktualizace
     * @return self Pro method chaining
     */
    public function setLastUpdate(string $lastUpdate): self
    {
        $this->config['lastUpdate'] = $lastUpdate;
        return $this;
    }

    /**
     * Vyrenderuje kompletní HTML headeru
     *
     * @return string Kompletní HTML kód headeru s AlpineJS funkcionalitou
     *
     * @example
     * ```php
     * $headerUI = new HeaderUI('users-header', $config);
     * // ... konfigurace ikony, titulu, tlačítek, atd.
     * echo $headerUI->render();
     * ```
     */
    public function render(): string
    {
        $html = $this->renderContainer();
        $html .= $this->renderHeader();
        $html .= $this->renderStats();
        $html .= $this->renderAlpineScript();
        $html .= '</div>';

        return $html;
    }

    private function renderContainer(): string
    {
        $functionName = str_replace(['-', ' '], '_', $this->id);
        return sprintf(
            '<div id="header-container-%s" class="%s" x-data="headerUI_%s()" x-cloak>',
            $this->id,
            $this->config['classes']['container'],
            $functionName
        );
    }

    private function renderHeader(): string
    {
        $title = $this->config['title'];
        $icon = $this->config['icon'];
        $subtitle = $this->config['subtitle'];

        $buttonsHtml = '';
        foreach ($this->buttons as $button) {
            $type = $button['options']['type'] ?? 'default';
            $colorClass = match($type) {
                'danger' => 'text-white bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 focus:ring-red-500',
                'warning' => 'text-white bg-gradient-to-r from-yellow-600 to-yellow-700 hover:from-yellow-700 hover:to-yellow-800 focus:ring-yellow-500',
                'success' => 'text-white bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 focus:ring-green-500',
                'primary' => 'text-white bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 focus:ring-blue-500',
                default => 'text-gray-700 bg-white border-gray-300 hover:bg-gray-50 focus:ring-blue-500'
            };

            $callback = $button['action'];
            $onclick = $callback(['header' => 'this']);

            $buttonsHtml .= sprintf(
                '<button id="%s" @click="%s" class="%s %s">
                    %s
                </button>',
                $button['id'],
                $onclick,
                $this->config['classes']['button'],
                $colorClass,
                $button['html']
            );
        }

        $subsHtml = '';
        foreach ($this->subs as $sub) {
            $type = $sub['type'] ?? 'default';
            $colorClass = match($type) {
                'danger' => 'bg-red-100 text-red-800',
                'warning' => 'bg-yellow-100 text-yellow-800',
                'success' => 'bg-green-100 text-green-800',
                'primary' => 'bg-blue-100 text-blue-800',
                default => 'bg-gray-100 text-gray-800'
            };

            $imageHtml = '';
            if (isset($sub['image'])) {
                $imageHtml = sprintf(
                    '<img src="%s" alt="" class="w-4 h-4 mr-2">',
                    htmlspecialchars($sub['image'])
                );
            }

            $subsHtml .= sprintf(
                '<span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium %s">
                    %s%s
                </span>',
                $colorClass,
                $imageHtml,
                htmlspecialchars($sub['text'])
            );
        }

        return sprintf(
            '<div class="%s">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0">
                            <div class="%s">
                                <i class="%s text-white text-lg"></i>
                            </div>
                        </div>
                        <div>
                            <div class="flex items-center space-x-3">
                                <h1 class="%s">%s</h1>
                                %s
                            </div>
                            <div class="flex items-center space-x-4 mt-1">
                                <p class="%s">
                                    <i class="fas fa-info-circle mr-1"></i>%s
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        %s
                    </div>
                </div>
            </div>',
            $this->config['classes']['header'],
            $this->config['classes']['icon'],
            $icon,
            $this->config['classes']['title'],
            htmlspecialchars($title),
            $subsHtml,
            $this->config['classes']['subtitle'],
            htmlspecialchars($subtitle),
            $buttonsHtml
        );
    }

    private function renderStats(): string
    {
        if (empty($this->config['stats']) && empty($this->config['lastUpdate'])) {
            return '';
        }

        $statsHtml = '';
        foreach ($this->config['stats'] as $key => $stat) {
            $type = $stat['type'] ?? 'blue';
            $colorClass = match($type) {
                'red' => 'bg-red-100 text-red-800',
                'yellow' => 'bg-yellow-100 text-yellow-800',
                'green' => 'bg-green-100 text-green-800',
                'purple' => 'bg-purple-100 text-purple-800',
                default => 'bg-blue-100 text-blue-800'
            };

            $statsHtml .= sprintf(
                '<div class="flex items-center space-x-2">
                    <span class="text-sm font-medium text-gray-700">%s:</span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium %s">
                        %s
                    </span>
                </div>',
                htmlspecialchars($stat['label']),
                $colorClass,
                htmlspecialchars($stat['count'])
            );
        }

        $lastUpdateHtml = '';
        if (!empty($this->config['lastUpdate'])) {
            $lastUpdateHtml = sprintf(
                '<div class="flex items-center space-x-2 text-sm text-gray-500">
                    <i class="fas fa-clock mr-1"></i>
                    <span>%s</span>
                </div>',
                htmlspecialchars($this->config['lastUpdate'])
            );
        }

        return sprintf(
            '<div class="%s">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-6">
                        %s
                    </div>
                    %s
                </div>
            </div>',
            $this->config['classes']['stats'],
            $statsHtml,
            $lastUpdateHtml
        );
    }

    private function renderAlpineScript(): string
    {
        $functionName = str_replace(['-', ' '], '_', $this->id);

        $html = <<<BLADE
        <script>
        function headerUI_{$functionName}() {
            return {
                // Methods
                init() {
                    // Inicializace headeru
                }
            }
        }
        </script>
        BLADE;

        return $html;
    }
}
