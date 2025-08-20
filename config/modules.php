<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Moduly - Základní konfigurace
    |--------------------------------------------------------------------------
    |
    | Tento soubor obsahuje základní konfiguraci pro modulární systém.
    |
    */

    // Cesta k složce s moduly
    'modules_path' => APP_ROOT . '/modules',

    // Automatické načítání modulů při bootu
    'auto_load' => true,

    // Automatické spouštění migrací pro nové moduly
    'auto_migrate' => true,

    // Automatické načítání překladů
    'auto_load_translations' => true,

    // Automatická registrace rout
    'auto_register_routes' => true,

    // Cache modulů
    'cache_enabled' => true,
    'cache_ttl' => 3600, // 1 hodina

    // Logování
    'logging' => [
        'enabled' => true,
        'level' => 'info', // debug, info, warning, error
    ],

    // Podporované jazyky pro překlady
    'supported_languages' => ['cs', 'en'],

    // Výchozí oprávnění pro moduly
    'default_permissions' => [
        'view' => ['admin', 'user'],
        'edit' => ['admin'],
        'delete' => ['admin'],
        'install' => ['admin'],
        'uninstall' => ['admin'],
        'enable' => ['admin'],
        'disable' => ['admin'],
    ],

    // Ignorované soubory při skenování modulů
    'ignored_files' => [
        '.git',
        '.gitignore',
        'node_modules',
        'vendor',
        'cache',
        'logs',
        'temp',
        'tests',
        'docs',
        'README.md',
        'composer.json',
        'composer.lock',
        'package.json',
        'package-lock.json',
    ],

    // Ignorované složky při skenování modulů
    'ignored_directories' => [
        '.git',
        'node_modules',
        'vendor',
        'cache',
        'logs',
        'temp',
        'tests',
        'docs',
    ],

    // Povolené přípony souborů pro skenování
    'allowed_file_extensions' => [
        'php',
        'blade.php',
        'js',
        'css',
        'json',
        'xml',
        'yaml',
        'yml',
    ],

    // Konfigurace pro jednotlivé typy modulů
    'module_types' => [
        'core' => [
            'path' => 'core',
            'auto_enable' => true,
            'can_disable' => false,
            'can_uninstall' => false,
        ],
        'feature' => [
            'path' => 'features',
            'auto_enable' => false,
            'can_disable' => true,
            'can_uninstall' => true,
        ],
        'plugin' => [
            'path' => 'plugins',
            'auto_enable' => false,
            'can_disable' => true,
            'can_uninstall' => true,
        ],
    ],

    // Hooky pro moduly
    'hooks' => [
        'before_boot' => [],
        'after_boot' => [],
        'before_request' => [],
        'after_request' => [],
        'before_response' => [],
        'after_response' => [],
    ],

    // Eventy pro moduly
    'events' => [
        'module.installed' => [],
        'module.uninstalled' => [],
        'module.enabled' => [],
        'module.disabled' => [],
        'module.updated' => [],
    ],

    // API konfigurace pro moduly
    'api' => [
        'enabled' => true,
        'prefix' => '/api/modules',
        'middleware' => ['auth', 'api'],
        'rate_limit' => 60, // requests per minute
    ],

    // Webhook konfigurace
    'webhooks' => [
        'enabled' => false,
        'url' => null,
        'secret' => null,
        'events' => [
            'module.installed',
            'module.uninstalled',
            'module.enabled',
            'module.disabled',
        ],
    ],

    // Backup konfigurace
    'backup' => [
        'enabled' => true,
        'path' => APP_ROOT . '/storage/backups/modules',
        'retention' => 30, // days
        'compress' => true,
    ],

    // Monitoring konfigurace
    'monitoring' => [
        'enabled' => true,
        'check_interval' => 300, // seconds
        'alerts' => [
            'module_failure' => true,
            'dependency_missing' => true,
            'permission_denied' => true,
        ],
    ],
];
