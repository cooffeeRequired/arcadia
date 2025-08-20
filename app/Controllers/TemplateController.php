<?php

namespace App\Controllers;

use Core\Http\Response;
use Core\Render\BaseController;
use Core\Services\ModuleManager;

class TemplateController extends BaseController
{
    private ModuleManager $moduleManager;

    public function __construct()
    {
        parent::__construct();
        $this->moduleManager = new ModuleManager();
    }

    /**
     * Zobrazí seznam šablon pro modul
     */
    public function index(string $moduleName): Response\ViewResponse
    {
        $modules = $this->moduleManager->getAllModules();
        $module = null;

        foreach ($modules as $mod) {
            if ($mod->getName() === $moduleName) {
                $module = $mod;
                break;
            }
        }

        if (!$module) {
            $this->redirect('/settings/modules');
        }

        return $this->view('settings.modules.templates.index', [
            'module' => $module
        ]);
    }

    /**
     * Zobrazí šablonu pro controller
     */
    public function controller(string $moduleName): Response\JsonResponse
    {
        $template = $this->getControllerTemplate($moduleName);

        return $this->json([
            'success' => true,
            'template' => $template
        ]);
    }

    /**
     * Zobrazí šablonu pro entity
     */
    public function entity(string $moduleName): Response\JsonResponse
    {
        $template = $this->getEntityTemplate($moduleName);

        return $this->json([
            'success' => true,
            'template' => $template
        ]);
    }

    /**
     * Zobrazí šablonu pro migrace
     */
    public function migration(string $moduleName): Response\JsonResponse
    {
        $template = $this->getMigrationTemplate($moduleName);

        return $this->json([
            'success' => true,
            'template' => $template
        ]);
    }

    /**
     * Zobrazí šablonu pro views
     */
    public function view(string $moduleName): Response\JsonResponse
    {
        $template = $this->getViewTemplate($moduleName);

        return $this->json([
            'success' => true,
            'template' => $template
        ]);
    }

    /**
     * Zobrazí šablonu pro překlady
     */
    public function translation(string $moduleName): Response\JsonResponse
    {
        $template = $this->getTranslationTemplate($moduleName);

        return $this->json([
            'success' => true,
            'template' => $template
        ]);
    }

    /**
     * Získá šablonu pro controller
     */
    private function getControllerTemplate(string $moduleName): string
    {
        $moduleNameUpper = ucfirst($moduleName);

        return <<<PHP
<?php

namespace Modules\\{$moduleNameUpper}\\Controllers;

use Core\\Render\\BaseController;
use Core\\Http\\Response\\ViewResponse;
use Core\\Http\\Response\\JsonResponse;
use Exception;

class {$moduleNameUpper}Controller extends BaseController
{
    /**
     * Zobrazí seznam položek
     */
    public function index(): ViewResponse
    {
        return \$this->view('modules.{$moduleName}.index');
    }

    /**
     * Zobrazí formulář pro vytvoření
     */
    public function create(): ViewResponse
    {
        return \$this->view('modules.{$moduleName}.create', [
            'title' => 'Nový {$moduleNameUpper}'
        ]);
    }

    /**
     * Uloží novou položku
     */
    public function store(): JsonResponse
    {
        try {
            \$data = \$this->request->getJson();

            // Zde implementujte logiku pro uložení

            return \$this->json([
                'success' => true,
                'message' => 'Položka byla úspěšně vytvořena'
            ]);
        } catch (Exception \$e) {
            return \$this->json([
                'success' => false,
                'message' => 'Chyba při vytváření položky: ' . \$e->getMessage()
            ], 500);
        }
    }

    /**
     * Zobrazí detail položky
     */
    public function show(\$id): ViewResponse
    {
        return \$this->view('modules.{$moduleName}.show', [
            'id' => \$id,
            'title' => 'Detail položky'
        ]);
    }

    /**
     * Zobrazí formulář pro editaci
     */
    public function edit(\$id): ViewResponse
    {
        return \$this->view('modules.{$moduleName}.edit', [
            'id' => \$id,
            'title' => 'Editace položky'
        ]);
    }

    /**
     * Aktualizuje položku
     */
    public function update(\$id): JsonResponse
    {
        try {
            \$data = \$this->request->getJson();

            // Zde implementujte logiku pro aktualizaci

            return \$this->json([
                'success' => true,
                'message' => 'Položka byla úspěšně aktualizována'
            ]);
        } catch (Exception \$e) {
            return \$this->json([
                'success' => false,
                'message' => 'Chyba při aktualizaci položky: ' . \$e->getMessage()
            ], 500);
        }
    }

    /**
     * Smaže položku
     */
    public function delete(\$id): JsonResponse
    {
        try {
            // Zde implementujte logiku pro smazání

            return \$this->json([
                'success' => true,
                'message' => 'Položka byla úspěšně smazána'
            ]);
        } catch (Exception \$e) {
            return \$this->json([
                'success' => false,
                'message' => 'Chyba při mazání položky: ' . \$e->getMessage()
            ], 500);
        }
    }
}
PHP;
    }

    /**
     * Získá šablonu pro entity
     */
    private function getEntityTemplate(string $moduleName): string
    {
        $moduleNameUpper = ucfirst($moduleName);
        $tableName = $moduleName . '_items';

        return <<<PHP
<?php

namespace Modules\\{$moduleNameUpper}\\Entities;

use Doctrine\\ORM\\Mapping as ORM;
use JsonSerializable;

#[ORM\\Entity]
#[ORM\\Table(name: '{$tableName}')]
class {$moduleNameUpper}Item implements JsonSerializable
{
    #[ORM\\Id]
    #[ORM\\GeneratedValue]
    #[ORM\\Column(type: 'integer')]
    protected int \$id;

    #[ORM\\Column(type: 'string', length: 255)]
    protected string \$name;

    #[ORM\\Column(type: 'text', nullable: true)]
    protected ?string \$description = null;

    #[ORM\\Column(type: 'string', length: 50)]
    protected string \$status = 'active';

    #[ORM\\Column(type: 'datetime')]
    protected \\DateTime \$created_at;

    #[ORM\\Column(type: 'datetime')]
    protected \\DateTime \$updated_at;

    public function __construct()
    {
        \$this->created_at = new \\DateTime();
        \$this->updated_at = new \\DateTime();
    }

    // Getters
    public function getId(): int
    {
        return \$this->id;
    }

    public function getName(): string
    {
        return \$this->name;
    }

    public function getDescription(): ?string
    {
        return \$this->description;
    }

    public function getStatus(): string
    {
        return \$this->status;
    }

    public function getCreatedAt(): \\DateTime
    {
        return \$this->created_at;
    }

    public function getUpdatedAt(): \\DateTime
    {
        return \$this->updated_at;
    }

    // Setters
    public function setName(string \$name): self
    {
        \$this->name = \$name;
        \$this->updated_at = new \\DateTime();
        return \$this;
    }

    public function setDescription(?string \$description): self
    {
        \$this->description = \$description;
        \$this->updated_at = new \\DateTime();
        return \$this;
    }

    public function setStatus(string \$status): self
    {
        \$this->status = \$status;
        \$this->updated_at = new \\DateTime();
        return \$this;
    }

    /**
     * Implementace JsonSerializable
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => \$this->id,
            'name' => \$this->name,
            'description' => \$this->description,
            'status' => \$this->status,
            'created_at' => \$this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => \$this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
PHP;
    }

    /**
     * Získá šablonu pro migrace
     */
    private function getMigrationTemplate(string $moduleName): string
    {
        $moduleNameUpper = ucfirst($moduleName);
        $tableName = $moduleName . '_items';

        return <<<PHP
<?php

class Install{$moduleNameUpper}
{
    private \$connection;
    private \$config;

    public function __construct()
    {
        \$this->config = require APP_ROOT . '/config/config.php';
        \$this->connection = \\Core\\Facades\\Container::get('doctrine.connection');
    }

    /**
     * Instaluje modul
     */
    public function install(): void
    {
        echo "Instalace modulu {$moduleNameUpper}...\\n";

        // Vytvoření tabulky pro modul
        \$this->create{$moduleNameUpper}Table();

        // Vložení výchozích dat
        \$this->insertDefaultData();

        // Vytvoření oprávnění
        \$this->createPermissions();

        echo "Modul {$moduleNameUpper} byl úspěšně nainstalován.\\n";
    }

    /**
     * Vytvoří tabulku pro modul
     */
    private function create{$moduleNameUpper}Table(): void
    {
        \$sql = "CREATE TABLE IF NOT EXISTS {$tableName} (
            id INT AUTO_INCREMENT NOT NULL,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            status VARCHAR(50) DEFAULT 'active',
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB";

        \$this->connection->executeStatement(\$sql);
        echo "Tabulka {$tableName} byla vytvořena.\\n";
    }

    /**
     * Vloží výchozí data
     */
    private function insertDefaultData(): void
    {
        \$sql = "INSERT INTO {$tableName} (name, description, status, created_at, updated_at) VALUES
                ('První položka', 'Toto je první položka', 'active', NOW(), NOW()),
                ('Druhá položka', 'Toto je druhá položka', 'active', NOW(), NOW())";

        \$this->connection->executeStatement(\$sql);
        echo "Výchozí data byla vložena.\\n";
    }

    /**
     * Vytvoří oprávnění pro modul
     */
    private function createPermissions(): void
    {
        // Zde by byla logika pro vytvoření oprávnění v systému
        echo "Oprávnění pro modul {$moduleNameUpper} byla vytvořena.\\n";
    }
}
PHP;
    }

    /**
     * Získá šablonu pro views
     */
    private function getViewTemplate(string $moduleName): string
    {
        $moduleNameUpper = ucfirst($moduleName);
        return <<<BLADE
@extends('layouts.app')

@section('title', '{$moduleNameUpper} - Arcadia')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-cube me-2"></i>
                        {$moduleNameUpper}
                    </h5>
                    <div class="btn-group">
                        <a href="/{$moduleName}/create" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-1"></i>
                            Nový
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Název</th>
                                    <th>Popis</th>
                                    <th>Stav</th>
                                    <th>Vytvořeno</th>
                                    <th>Akce</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Zde budou položky -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
BLADE;
    }

    /**
     * Získá šablonu pro překlady
     */
    private function getTranslationTemplate(string $moduleName): string
    {
        $moduleNameUpper = ucfirst($moduleName);
        return <<<PHP
<?php

return [
    'title' => '{$moduleNameUpper}',
    'description' => 'Správa {$moduleName}',
    'create' => 'Vytvořit nový {$moduleName}',
    'edit' => 'Upravit {$moduleName}',
    'delete' => 'Smazat {$moduleName}',
    'save' => 'Uložit',
    'cancel' => 'Zrušit',
    'back' => 'Zpět',
    'name' => 'Název',
    'description' => 'Popis',
    'status' => 'Stav',
    'created_at' => 'Vytvořeno',
    'updated_at' => 'Aktualizováno',
    'actions' => 'Akce',
    'no_items' => 'Žádné položky nebyly nalezeny',
    'item_created' => 'Položka byla úspěšně vytvořena',
    'item_updated' => 'Položka byla úspěšně aktualizována',
    'item_deleted' => 'Položka byla úspěšně smazána',
    'confirm_delete' => 'Opravdu chcete smazat tuto položku?',
];
PHP;
    }
}
