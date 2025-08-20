<?php

namespace Core\Helpers;

use Core\Http\Request;

class SidebarHelper
{

    /**
     * Získá menu položky pro sidebar
     */
    public static function getSidebarMenu(): array
    {
        $menuItems = [];

        // Získáme aktuální URI
        $currentUri = Request::getInstance()->getUri() ?? '/';

        // Dashboard - první položka
        $menuItems[] = [
            'name' => i18('dashboard'),
            'icon' => 'home',
            'url' => '/',
            'active' => $currentUri === '/' || $currentUri === '/home',
            'color' => 'blue'
        ];

        // Základní funkce aplikace
        $menuItems[] = [
            'name' => i18('customers'),
            'icon' => 'users',
            'url' => '/customers',
            'active' => str_starts_with($currentUri, '/customers'),
            'color' => 'green'
        ];

        $menuItems[] = [
            'name' => i18('contacts'),
            'icon' => 'user',
            'url' => '/contacts',
            'active' => str_starts_with($currentUri, '/contacts'),
            'color' => 'blue'
        ];

        $menuItems[] = [
            'name' => i18('deals'),
            'icon' => 'handshake',
            'url' => '/deals',
            'active' => str_starts_with($currentUri, '/deals'),
            'color' => 'purple'
        ];

        $menuItems[] = [
            'name' => i18('projects'),
            'icon' => 'folder',
            'url' => '/projects',
            'active' => str_starts_with($currentUri, '/projects'),
            'color' => 'orange'
        ];

        $menuItems[] = [
            'name' => i18('invoices'),
            'icon' => 'file-invoice',
            'url' => '/invoices',
            'active' => str_starts_with($currentUri, '/invoices'),
            'color' => 'red'
        ];

        $menuItems[] = [
            'name' => i18('reports'),
            'icon' => 'chart-bar',
            'url' => '/reports',
            'active' => str_starts_with($currentUri, '/reports'),
            'color' => 'indigo'
        ];

        $menuItems[] = [
            'name' => i18('workflows'),
            'icon' => 'sitemap',
            'url' => '/workflows',
            'active' => str_starts_with($currentUri, '/workflows'),
            'color' => 'teal'
        ];

        $menuItems[] = [
            'name' => i18('emails'),
            'icon' => 'envelope',
            'url' => '/emails',
            'active' => str_starts_with($currentUri, '/emails'),
            'color' => 'pink'
        ];

// TODO -> implement moduiles

//        // Sekce Moduly s registrovánými moduly (pouze ty ze složky /modules)
//        $moduleManager = self::getModuleManager();
//        $availableModules = $moduleManager->availableModules();
//
//        if (!empty($availableModules)) {
//            $modulesSubmenu = [];
//
//            foreach ($availableModules as $moduleName) {
//                // Přeskočíme základní moduly, které už jsou v hlavním menu
//                if (in_array($moduleName, ['customers', 'contacts', 'deals', 'projects', 'invoices', 'reports', 'workflows', 'emails'])) {
//                    continue;
//                }
//
//                $config = $moduleManager->moduleConfig($moduleName);
//                $displayName = $config['display_name'] ?? ucfirst($moduleName);
//                $isEnabled = $config['is_enabled'] ?? false;
//
//                $modulesSubmenu[] = [
//                    'name' => $displayName,
//                    'icon' => self::getModuleIcon($moduleName),
//                    'url' => '/' . $moduleName,
//                    'active' => str_starts_with($currentUri, '/' . $moduleName),
//                    'color' => self::getModuleColor($moduleName),
//                    'badge' => $isEnabled ? null : ['text' => 'Vypnuto', 'color' => 'gray']
//                ];
//            }
//
//            if (!empty($modulesSubmenu)) {
//                $menuItems[] = [
//                    'name' => 'Moduly',
//                    'icon' => 'puzzle-piece',
//                    'url' => '#',
//                    'active' => self::isAnyModuleActive($modulesSubmenu),
//                    'color' => 'yellow',
//                    'submenu' => $modulesSubmenu
//                ];
//            }
//        }

        return $menuItems;
    }


    /**
     * Získá submenu pro nastavení
     * @noinspection PhpUnusedPrivateMethodInspection
     */
    private static function getSettingsSubmenu(): array
    {
         // $currentUri = Request::getInstance()->getUri() ?? '/';

//        $submenu = [
//            [
//                'name' => 'Obecné',
//                'url' => '/settings',
//                'active' => $currentUri === '/settings',
//                'icon' => 'settings'
//            ],
//            [
//                'name' => 'Profil',
//                'url' => '/settings/profile',
//                'active' => str_starts_with($currentUri, '/settings/profile'),
//                'icon' => 'user'
//            ],
//            [
//                'name' => 'Systém',
//                'url' => '/settings/system',
//                'active' => str_starts_with($currentUri, '/settings/system'),
//                'icon' => 'server'
//            ]
//        ];

//        // Přidání správy modulů, pokud má uživatel oprávnění
//        if ($moduleManager->hasPermission('settings', 'view', 'admin')) {
//            $submenu[] = [
//                'name' => 'Moduly',
//                'url' => '/settings/modules',
//                'active' => str_starts_with($currentUri, '/settings/modules'),
//                'icon' => 'cube',
//                'badge' => self::getModulesBadge()
//            ];
//        }

        return [];
    }



    public static function isLinkActive(string $module): bool
    {
        $currentUri = $_SERVER['REQUEST_URI'] ?? '/';
        return str_starts_with($currentUri, '/' . $module);
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    private static function isAnyLinkActive(array $linksModules): bool
    {
        $currentUri = Request::getInstance()->getUri() ?? '/';
        if (str_starts_with($currentUri, '/settings/modules')) {
            return false;
        }
        return array_any($linksModules, fn($link) => $link['active']);

    }
}
