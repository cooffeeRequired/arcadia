# Modulární systém Arcadia CRM

## Přehled

Byl vytvořen kompletní modulární systém pro Arcadia CRM, který umožňuje zapínat a vypínat různé funkcionality podle potřeb. Systém zahrnuje modul pro projekty s pokročilými funkcemi a možnost správy všech modulů.

## Vytvořené komponenty

### 1. Konfigurace modulů (`config/modules.php`)

Konfigurační soubor definuje:
- **enabled**: Seznam povolených modulů
- **dependencies**: Závislosti mezi moduly
- **settings**: Nastavení jednotlivých modulů
- **permissions**: Oprávnění pro různé role

### 2. Správa modulů (`core/Modules/ModuleManager.php`)

Třída pro správu modulů s metodami:
- `isEnabled()` - Kontrola, zda je modul povolen
- `isAvailable()` - Kontrola dostupnosti modulu
- `modulesWithMissingDependencies()` - Kontrola závislostí
- `hasPermission()` - Kontrola oprávnění
- `getSetting()` - Získání nastavení modulu
- `moduleConfig()` - Získání konfigurace modulu
- `scanAndSync()` - Skenování a synchronizace modulů
- `autoInsertNew()` - Automatické vkládání nových modulů

### 3. Entita Project (`app/Entities/Project.php`)

Hlavní entita pro projekty s vazbami na:
- Zákazníky (Customer)
- Faktury (Invoice)
- Aktivity (Activity)
- Události (ProjectEvent)
- Časové záznamy (ProjectTimeEntry)
- Soubory (ProjectFile)

### 4. Související entity

#### ProjectEvent
- Události související s projekty
- Typy: milestone, meeting, deadline, delivery, review
- Stav: pending, in_progress, completed, cancelled

#### ProjectTimeEntry
- Sledování času stráveného na projektech
- Automatický výpočet nákladů
- Možnost pauzování a obnovení

#### ProjectFile
- Správa souborů souvisejících s projekty
- Podpora různých typů souborů
- Automatické generování náhledů

### 5. Controllers

#### ProjectController
- CRUD operace pro projekty
- Podpora modulárního systému
- API endpointy
- Mini stránky pro projekty

#### ModuleController
- Správa modulů
- Zapínání/vypínání modulů
- Konfigurace nastavení
- Logování změn

### 6. Middleware (`core/Middleware/ModuleMiddleware.php`)

Automatická kontrola dostupnosti modulů a oprávnění.

### 7. Helper (`core/Helpers/ModuleHelper.php`)

Užitečné metody pro views a templates.

### 8. Sidebar Helper (`core/Helpers/SidebarHelper.php`)

Automatické generování sidebar menu podle dostupných modulů.

## Funkcionality modulu Projektů

### Základní funkce
- ✅ Vytvoření, editace, mazání projektů
- ✅ Přiřazení zákazníka a manažera
- ✅ Nastavení termínů a rozpočtu
- ✅ Sledování průběhu a nákladů
- ✅ Filtrování a vyhledávání

### Pokročilé funkce
- ✅ **Mini stránky**: Veřejné stránky pro klienty
- ✅ **Události**: Milestone, schůzky, termíny
- ✅ **Časové záznamy**: Sledování práce
- ✅ **Soubory**: Správa dokumentů
- ✅ **API**: REST API pro integrace

### Nastavení modulu
- `enable_mini_pages`: Zapnutí mini stránek
- `enable_events`: Zapnutí událostí
- `enable_time_tracking`: Zapnutí sledování času
- `enable_file_attachments`: Zapnutí souborů

## Oprávnění

### Projekty
- **view**: admin, manager, user
- **create**: admin, manager
- **edit**: admin, manager
- **delete**: admin
- **manage_events**: admin, manager

### Reporty
- **view**: admin, manager
- **create**: admin
- **export**: admin, manager

### E-mail Workflow
- **view**: admin, manager
- **create**: admin
- **edit**: admin
- **delete**: admin

## Závislosti modulů

```
projects → customers, invoices, activities
reports → customers, deals, contacts, invoices, projects
email_workflow → customers, contacts
invoices → customers
deals → customers, contacts
activities → customers, contacts
```

## Routy

### Projekty
```
GET    /projects              - Seznam projektů
GET    /projects/create       - Formulář pro vytvoření
POST   /projects              - Vytvoření projektu
GET    /projects/{id}         - Detail projektu
GET    /projects/{id}/edit    - Formulář pro editaci
POST   /projects/{id}         - Aktualizace projektu
POST   /projects/{id}/delete  - Smazání projektu
GET    /projects/{id}/api     - API endpoint
GET    /projects/mini/{slug}  - Mini stránka
```

### Moduly (v nastavení)
```
GET    /settings/modules               - Seznam modulů
GET    /settings/modules/{module}      - Detail modulu
GET    /settings/modules/{module}/edit - Editace modulu
POST   /settings/modules/{module}      - Aktualizace modulu
POST   /settings/modules/{module}/toggle - Zapnutí/vypnutí
GET    /settings/modules/{module}/dependencies - Závislosti
GET    /settings/modules/api/status    - API status
GET    /settings/modules/log           - Log modulů
```

## Views

### Projekty
- `projects/index.blade.php` - Seznam projektů s filtry
- `projects/create.blade.php` - Vytvoření projektu
- `projects/show.blade.php` - Detail projektu
- `projects/mini-page.blade.php` - Veřejná mini stránka

### Moduly (v nastavení)
- `modules/index.blade.php` - Správa modulů
- `modules/show.blade.php` - Detail modulu
- `modules/edit.blade.php` - Editace modulu
- `layouts/sidebar.blade.php` - Automatické generování sidebar menu

## Použití

### 1. Zapnutí modulu
V `config/modules.php` nastavte:
```php
'enabled' => [
    'projects' => true,
    // ...
],
```

### 2. Kontrola v kódu

```php
use Core\Modules\ModuleManager;
use Core\Facades\Container;

$moduleManager = Container::get(ModuleManager::class, ModuleManager::class);

if ($moduleManager->isAvailable('projects')) {
    // Modul je dostupný
}

if ($moduleManager->hasPermission('projects', 'create')) {
    // Uživatel může vytvářet projekty
}
```

### 3. V views
```php
@if($moduleManager->hasPermission('projects', 'create'))
    <a href="{{ route('projects.create') }}">Nový projekt</a>
@endif
```

## Výhody modulárního systému

1. **Flexibilita**: Možnost zapínat/vypínat funkce podle potřeb
2. **Škálovatelnost**: Snadné přidávání nových modulů
3. **Závislosti**: Automatická kontrola závislostí
4. **Oprávnění**: Granulární kontrola přístupu
5. **Konfigurace**: Nastavitelná funkcionalita modulů
6. **Architektura**: Clean Architecture s Dependency Injection
7. **Testovatelnost**: Snadné testování jednotlivých komponent
8. **Rozšiřitelnost**: Možnost přidávání nových funkcí

## Nové API

### ModuleManager
- `isEnabled(string $module): bool` - Kontrola, zda je modul povolen
- `isInstalled(string $module): bool` - Kontrola, zda je modul nainstalován
- `isAvailable(string $module): bool` - Kontrola dostupnosti modulu
- `getSetting(string $module, string $key, mixed $default = null): mixed` - Získání nastavení
- `hasPermission(string $module, string $permission, ?string $role = null): bool` - Kontrola oprávnění
- `enabledModules(): array` - Seznam povolených modulů
- `availableModules(): array` - Seznam dostupných modulů
- `modulesWithMissingDependencies(): array` - Moduly s chybějícími závislostmi
- `moduleConfig(string $module): array` - Konfigurace modulu
- `scanAndSync(): array` - Skenování a synchronizace
- `autoInsertNew(): array` - Automatické vkládání nových modulů
- `runModuleMigrations(string $module): void` - Spuštění migrací
- `rollbackModuleMigrations(string $module): void` - Vrácení migrací zpět
- `getModuleMigrationStatus(string $module): array` - Stav migrací

### Services
- `ModuleService` - Základní služby pro moduly
- `ModuleCatalog` - Katalog modulů (FS ↔ DB)
- `MigrationService` - Správa migrací
- `DependencyChecker` - Kontrola závislostí

### Infrastructure
- `DoctrineModuleRepository` - Repository pro moduly
- `FilesystemScanner` - Skenování souborového systému
- `PhpConfigLoader` - Načítání konfigurace z PHP souborů

### DTOs
- `ControllerInfo` - Informace o controllerech
- `EntityInfo` - Informace o entitách
- `MigrationInfo` - Informace o migracích
