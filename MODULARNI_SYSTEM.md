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
- `checkDependencies()` - Kontrola závislostí
- `hasPermission()` - Kontrola oprávnění
- `getSetting()` - Získání nastavení modulu

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

$moduleManager = new ModuleManager();

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
6. **Logování**: Sledování změn v modulích
7. **Automatický sidebar**: Menu se generuje podle dostupných modulů
8. **Integrace do nastavení**: Správa modulů je součástí nastavení systému

## Budoucí rozšíření

### Možné nové moduly
- **Kanban**: Sledování úkolů
- **Kalendář**: Plánování událostí
- **Chat**: Interní komunikace
- **Analytics**: Pokročilé reporty
- **Integrations**: API integrace

### Vylepšení
- Drag & drop pro soubory
- Real-time notifikace
- Export do PDF/Excel
- Automatické zálohování
- Multi-tenant podpora

## Instalace

1. Zkopírujte všechny soubory do příslušných adresářů
2. Spusťte migrace pro vytvoření tabulek
3. Nakonfigurujte moduly v `config/modules.php`
4. Nastavte oprávnění pro uživatele

## Podpora

Systém je plně dokumentovaný a připravený pro produkční nasazení. Všechny komponenty jsou testovány a optimalizovány pro výkon.
