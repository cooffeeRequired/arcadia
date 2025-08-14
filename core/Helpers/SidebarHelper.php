<?php

namespace Core\Helpers;

use Core\Modules\ModuleManager;
use Core\Facades\Container;

class SidebarHelper
{
    private static ?ModuleManager $moduleManager = null;

    /**
     * Získá instanci ModuleManager
     */
    private static function getModuleManager(): ModuleManager
    {
        if (self::$moduleManager === null) {
            self::$moduleManager = new ModuleManager();
        }
        return self::$moduleManager;
    }

    /**
     * Získá menu položky pro sidebar
     */
    public static function getSidebarMenu(): array
    {
        $menuItems = [];

        // Získáme aktuální URI
        $currentUri = $_SERVER['REQUEST_URI'] ?? '/';

        // Základní menu položky
        $menuItems[] = [
            'name' => 'Dashboard',
            'icon' => 'home',
            'url' => '/',
            'active' => $currentUri === '/' || $currentUri === '/home',
            'color' => 'blue'
        ];

        // Sekce Moduly s registrovánými moduly (pouze ty ze složky /modules)
        $moduleManager = self::getModuleManager();
        $registeredModules = $moduleManager->getAllModulesConfig();

        if (!empty($registeredModules)) {
            $modulesSubmenu = [];

            foreach ($registeredModules as $moduleName => $config) {
                // Přeskočíme základní moduly, které už jsou v hlavním menu
                if (in_array($moduleName, ['customers', 'contacts', 'deals', 'projects', 'invoices', 'reports', 'workflows', 'emails'])) {
                    continue;
                }

                $modulesSubmenu[] = [
                    'name' => $config['display_name'] ?? ucfirst($moduleName),
                    'icon' => self::getModuleIcon($moduleName),
                    'url' => '/' . $moduleName,
                    'active' => str_starts_with($currentUri, '/' . $moduleName),
                    'color' => self::getModuleColor($moduleName),
                    'badge' => $config['enabled'] ? null : ['text' => 'Vypnuto', 'color' => 'gray']
                ];
            }

            if (!empty($modulesSubmenu)) {
                $menuItems[] = [
                    'name' => 'Moduly',
                    'icon' => 'puzzle-piece',
                    'url' => '#',
                    'active' => str_starts_with($currentUri, '/settings/modules'),
                    'color' => 'yellow',
                    'submenu' => $modulesSubmenu
                ];
            }
        }

        return $menuItems;
    }

    /**
     * Získá ikonu modulu
     */
    private static function getModuleIcon(string $module): string
    {
        $icons = [
            'projects' => 'folder',
            'reports' => 'chart-bar',
            'email_workflow' => 'envelope',
            'invoices' => 'file-invoice',
            'customers' => 'users',
            'contacts' => 'user',
            'deals' => 'handshake',
            'activities' => 'calendar',
            'workflows' => 'sitemap',
            'emails' => 'envelope'
        ];

        // Fallback ikony pro případ, že modul není v poli
        $fallbackIcons = [
            'reports' => 'chart-bar',
            'email-workflow' => 'envelope',
            'email_workflow' => 'envelope',
            'invoices' => 'file-invoice',
            'deals' => 'handshake',
            'activities' => 'calendar'
        ];

        // Nejdříve zkusíme hlavní pole
        if (isset($icons[$module])) {
            return $icons[$module];
        }

        // Pak zkusíme fallback pole
        if (isset($fallbackIcons[$module])) {
            return $fallbackIcons[$module];
        }

        // Nakonec vrátíme výchozí ikonu
        return 'box';
    }

    /**
     * Získá barvu modulu
     */
    private static function getModuleColor(string $module): string
    {
        $colors = [
            'projects' => 'blue',
            'reports' => 'green',
            'email_workflow' => 'purple',
            'invoices' => 'orange',
            'customers' => 'indigo',
            'contacts' => 'pink',
            'deals' => 'teal',
            'activities' => 'yellow',
            'workflows' => 'cyan',
            'emails' => 'purple'
        ];

        return $colors[$module] ?? 'gray';
    }

    /**
     * Získá badge pro modul (počet položek, notifikace, atd.)
     */
    private static function getModuleBadge(string $module): ?array
    {
        $moduleManager = self::getModuleManager();

        // Kontrola oprávnění
        if (!$moduleManager->hasPermission($module, 'view')) {
            return null;
        }

        // Zde můžete přidat logiku pro získání počtu položek
        // Například: počet nových projektů, nevyřízených faktur, atd.

        return null;
    }

    /**
     * Získá submenu pro nastavení
     */
    private static function getSettingsSubmenu(): array
    {
        $moduleManager = self::getModuleManager();
        $currentUri = $_SERVER['REQUEST_URI'] ?? '/';

        $submenu = [
            [
                'name' => 'Obecné',
                'url' => '/settings',
                'active' => $currentUri === '/settings',
                'icon' => 'settings'
            ],
            [
                'name' => 'Profil',
                'url' => '/settings/profile',
                'active' => str_starts_with($currentUri, '/settings/profile'),
                'icon' => 'user'
            ],
            [
                'name' => 'Systém',
                'url' => '/settings/system',
                'active' => str_starts_with($currentUri, '/settings/system'),
                'icon' => 'server'
            ]
        ];

        // Přidání správy modulů, pokud má uživatel oprávnění
        if ($moduleManager->hasPermission('settings', 'view', 'admin')) {
            $submenu[] = [
                'name' => 'Moduly',
                'url' => '/settings/modules',
                'active' => str_starts_with($currentUri, '/settings/modules'),
                'icon' => 'cube',
                'badge' => self::getModulesBadge()
            ];
        }

        return $submenu;
    }

    /**
     * Získá badge pro moduly (počet problémů)
     */
    private static function getModulesBadge(): ?array
    {
        $moduleManager = self::getModuleManager();
        $modulesWithIssues = $moduleManager->getModulesWithMissingDependencies();

        if (empty($modulesWithIssues)) {
            return null;
        }

        return [
            'text' => count($modulesWithIssues),
            'color' => 'red',
            'tooltip' => 'Moduly s problémy'
        ];
    }

    /**
     * Zkontroluje, zda je modul aktivní v sidebar
     */
    public static function isModuleActive(string $module): bool
    {
        $currentUri = $_SERVER['REQUEST_URI'] ?? '/';
        return str_starts_with($currentUri, '/' . $module);
    }

    /**
     * Získá CSS třídy pro aktivní položku
     */
    public static function getActiveClasses(string $module): string
    {
        return self::isModuleActive($module) ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900';
    }

    /**
     * Získá kompletní sidebar HTML
     */
    public static function renderSidebar(): string
    {
        $menuItems = self::getSidebarMenu();
        $html = '<nav class="space-y-1">';

        foreach ($menuItems as $item) {
            $html .= self::renderMenuItem($item);
        }

        $html .= '</nav>';
        return $html;
    }

    /**
     * Vykreslí jednotlivou položku menu
     */
    private static function renderMenuItem(array $item): string
    {
        $activeClass = $item['active'] ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900';
        $iconColor = 'text-' . $item['color'] . '-600';

        $html = '<a href="' . $item['url'] . '" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md ' . $activeClass . '">';
        $html .= '<i class="fas fa-' . $item['icon'] . ' mr-3 ' . $iconColor . '"></i>';
        $html .= $item['name'];

        if (isset($item['badge']) && $item['badge']) {
            $badgeColor = 'bg-' . $item['badge']['color'] . '-100 text-' . $item['badge']['color'] . '-800';
            $html .= '<span class="ml-auto inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' . $badgeColor . '">';
            $html .= $item['badge']['text'];
            $html .= '</span>';
        }

        $html .= '</a>';

        // Submenu
        if (isset($item['submenu'])) {
            $html .= '<div class="ml-4 space-y-1">';
            foreach ($item['submenu'] as $subItem) {
                $subActiveClass = $subItem['active'] ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900';
                $html .= '<a href="' . $subItem['url'] . '" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md ' . $subActiveClass . '">';
                $html .= '<i class="fas fa-' . $subItem['icon'] . ' mr-3 text-gray-400"></i>';
                $html .= $subItem['name'];

                if (isset($subItem['badge']) && $subItem['badge']) {
                    $badgeColor = 'bg-' . $subItem['badge']['color'] . '-100 text-' . $subItem['badge']['color'] . '-800';
                    $html .= '<span class="ml-auto inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' . $badgeColor . '">';
                    $html .= $subItem['badge']['text'];
                    $html .= '</span>';
                }

                $html .= '</a>';
            }
            $html .= '</div>';
        }

        return $html;
    }
}
