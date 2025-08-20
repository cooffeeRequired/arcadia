# Implementace načítání modulů při bootu aplikace

## Přehled

Tento dokument popisuje implementaci automatického načítání modulů při bootu aplikace Arcadia CRM.

## Komponenty

### 1. ModuleServiceProvider (`core/Providers/ModuleServiceProvider.php`)

Hlavní provider pro načítání modulů při bootu aplikace.

**Funkce:**
- `boot()` - Hlavní metoda pro načítání modulů
- `registerModuleRoutes()` - Registrace rout pro moduly
- `loadModuleTranslations()` - Načítání překladů
- `runModuleMigrations()` - Spouštění migrací

### 2. ModuleMiddleware (`core/Middleware/ModuleMiddleware.php`)

Middleware pro kontrolu modulů při každém requestu.

**Funkce:**
- `handle()` - Spuštění middleware
- `checkModuleAccess()` - Kontrola přístupu k modulu
- `checkModulePermission()` - Kontrola oprávnění
- `getActiveModules()` - Získání aktivních modulů

### 3. Konfigurace (`config/modules.php`)

Konfigurační soubor pro modulární systém.

**Klíčové nastavení:**
- `auto_load` - Automatické načítání při bootu
- `auto_migrate` - Automatické spouštění migrací
- `auto_load_translations` - Automatické načítání překladů
- `ignored_directories` - Ignorované složky

### 4. Helper funkce (`core/helpers.php`)

Helper funkce pro snadný přístup k modulům.

**Funkce:**
- `modules()` - Získá všechny moduly
- `active_modules()` - Získá aktivní moduly
- `module($name)` - Získá konkrétní modul
- `is_module_active($name)` - Kontrola aktivního modulu
- `module_asset($moduleName, $path)` - Asset soubory modulu
- `module_view($moduleName, $view)` - View soubory modulu

## Implementace v bootstrap.php

```php
// Načtení modulů při bootu aplikace
ModuleServiceProvider::boot();

// Spuštění middleware pro moduly
ModuleMiddleware::handle();
```

## Průběh načítání

### 1. Boot aplikace
1. `Bootstrap::autoload()` - Načtení autoloaderu a konfigurace
2. `Bootstrap::init()` - Inicializace základních služeb
3. `Bootstrap::tryMakeConnection()` - Připojení k databázi
4. **`ModuleServiceProvider::boot()`** - Načtení modulů
5. **`ModuleMiddleware::handle()`** - Spuštění middleware

### 2. Načítání modulů
1. Vytvoření instance `ModuleManager`
2. Registrace do containeru
3. Načtení modulů ze souborů (`loadModulesFromFiles()`)
4. Synchronizace s databází
5. Registrace modulů do containeru
6. Načtení aktivních modulů
7. Logování informací

### 3. Middleware kontrola
1. Kontrola, zda jsou moduly již načteny
2. Pokud ne, načtení modulů
3. Registrace do containeru
4. Logování pro debugging

## Použití v aplikaci

### V Controllerech
```php
public function modules(): Response\ViewResponse
{
    // Použití helper funkce
    $modules = modules();

    return $this->view('settings.modules.index', [
        'modules' => $modules
    ]);
}
```

### V Views
```php
@if(is_module_active('example'))
    <!-- Obsah pro aktivní modul -->
@endif

@foreach(active_modules() as $module)
    <div>{{ $module->getDisplayName() }}</div>
@endforeach
```

### V kódu
```php
// Získání modulu
$module = module('example');

// Kontrola aktivního modulu
if (is_module_active('example')) {
    // Logika pro aktivní modul
}

// Asset soubory modulu
$cssUrl = module_asset('example', 'css/style.css');
```

## Konfigurace

### Základní nastavení
```php
// config/modules.php
return [
    'auto_load' => true,
    'auto_migrate' => true,
    'auto_load_translations' => true,
    'cache_enabled' => true,
    'logging' => [
        'enabled' => true,
        'level' => 'info',
    ],
];
```

### Ignorované složky
```php
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
```

## Logování

### Development prostředí
```
[ModuleServiceProvider] Moduly načteny: 3 celkem, 2 aktivních
[ModuleServiceProvider] Registrován controller: ExampleController (Modules\Example\Controllers)
[ModuleServiceProvider] Načteny překlady pro modul example (cs)
[ModuleMiddleware] Moduly načteny v middleware: 3 celkem, 2 aktivních
```

### Production prostředí
- Logování pouze do souboru
- Minimální informace pro výkon

## Error handling

### Graceful degradation
- Chyby při načítání modulů neukončí aplikaci
- Logování chyb pro debugging
- Fallback na výchozí konfiguraci

### Typy chyb
- Chybějící config.php soubor
- Neplatná konfigurace modulu
- Chyby při synchronizaci s databází
- Chyby při načítání překladů

## Výkon

### Optimalizace
- Cache modulů (TTL: 1 hodina)
- Lazy loading při prvním requestu
- Kontrola existence před načítáním
- Ignorování nepotřebných složek

### Monitoring
- Sledování času načítání
- Počet načtených modulů
- Chyby při načítání
- Výkon jednotlivých modulů

## Rozšíření

### Hooky
```php
'hooks' => [
    'before_boot' => [],
    'after_boot' => [],
    'before_request' => [],
    'after_request' => [],
],
```

### Eventy
```php
'events' => [
    'module.installed' => [],
    'module.uninstalled' => [],
    'module.enabled' => [],
    'module.disabled' => [],
],
```

### API
```php
'api' => [
    'enabled' => true,
    'prefix' => '/api/modules',
    'middleware' => ['auth', 'api'],
    'rate_limit' => 60,
],
```

## Testování

### Unit testy
- Testování ModuleServiceProvider
- Testování ModuleMiddleware
- Testování helper funkcí
- Testování konfigurace

### Integration testy
- Testování načítání modulů
- Testování synchronizace s databází
- Testování routování
- Testování překladů

## Bezpečnost

### Oprávnění
- Kontrola oprávnění pro každý modul
- Role-based access control
- Audit log pro změny modulů

### Validace
- Validace konfigurace modulů
- Kontrola závislostí
- Bezpečnostní kontroly souborů

## Monitoring a maintenance

### Logy
- Logování načítání modulů
- Logování chyb
- Logování změn stavu

### Backup
- Automatický backup modulů
- Retention policy (30 dní)
- Komprese backupů

### Alerting
- Upozornění na chyby modulů
- Upozornění na chybějící závislosti
- Upozornění na neoprávněný přístup
