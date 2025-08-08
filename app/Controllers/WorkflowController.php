<?php

namespace App\Controllers;

use App\Entities\Workflow;
use App\Entities\WorkflowExecution;
use App\Entities\User;
use Core\Facades\Container;
use Core\Render\View;
use Illuminate\Support\Facades\Validator;

class WorkflowController
{
    private $entityManager;

    public function __construct()
    {
        $this->entityManager = Container::get('doctrine.em');
    }

    public function index()
    {
        $workflows = $this->entityManager->getRepository(Workflow::class)
            ->createQueryBuilder('w')
            ->orderBy('w.priority', 'DESC')
            ->addOrderBy('w.created_at', 'DESC')
            ->getQuery()
            ->getResult();

        $stats = [
            'total' => count($workflows),
            'active' => count(array_filter($workflows, fn($w) => $w->isActive())),
            'inactive' => count(array_filter($workflows, fn($w) => !$w->isActive()))
        ];

        return View::render('workflows/index', [
            'title' => 'Workflow',
            'workflows' => $workflows,
            'stats' => $stats
        ]);
    }

    public function create()
    {
        $triggerTypes = [
            'customer_created' => 'Nový zákazník',
            'customer_updated' => 'Zákazník aktualizován',
            'deal_created' => 'Nový obchod',
            'deal_stage_changed' => 'Změna fáze obchodu',
            'deal_won' => 'Obchod vyhrán',
            'deal_lost' => 'Obchod prohrán',
            'contact_added' => 'Přidán kontakt',
            'invoice_created' => 'Vytvořena faktura',
            'invoice_paid' => 'Faktura zaplacena'
        ];

        $actionTypes = [
            'send_email' => 'Odeslat e-mail',
            'create_task' => 'Vytvořit úkol',
            'update_status' => 'Aktualizovat status',
            'add_note' => 'Přidat poznámku',
            'assign_user' => 'Přiřadit uživatele',
            'send_notification' => 'Odeslat notifikaci',
            'create_deal' => 'Vytvořit obchod',
            'update_customer' => 'Aktualizovat zákazníka'
        ];

        return View::render('workflows/create', [
            'title' => 'Vytvořit workflow',
            'triggerTypes' => $triggerTypes,
            'actionTypes' => $actionTypes
        ]);
    }

    public function store()
    {
        // $validator = new Validator($_POST);
        // $validator->required(['name', 'trigger_type'])
        //          ->maxLength('name', 255)
        //          ->maxLength('description', 1000);

        // if ($validator->fails()) {
        //     return View::render('workflows/create', [
        //         'title' => 'Vytvořit workflow',
        //         'errors' => $validator->errors(),
        //         'old' => $_POST
        //     ]);
        // }

        $workflow = new Workflow();
        $workflow->setName($_POST['name'])
                ->setDescription($_POST['description'] ?? null)
                ->setTriggerType($_POST['trigger_type'])
                ->setTriggerConfig(json_decode($_POST['trigger_config'] ?? '{}', true))
                ->setConditions(json_decode($_POST['conditions'] ?? '[]', true))
                ->setActions(json_decode($_POST['actions'] ?? '[]', true))
                ->setIsActive(isset($_POST['is_active']))
                ->setPriority((int)($_POST['priority'] ?? 0))
                ->setCreatedBy($this->getCurrentUser());

        $this->entityManager->persist($workflow);
        $this->entityManager->flush();

        header('Location: /workflows');
        exit;
    }

    public function show($id)
    {
        $workflow = $this->entityManager->find(Workflow::class, $id);
        if (!$workflow) {
            header('Location: /workflows');
            exit;
        }

        // Získat poslední spuštění
        $executions = $this->entityManager->getRepository(WorkflowExecution::class)
            ->createQueryBuilder('e')
            ->where('e.workflow = :workflow')
            ->setParameter('workflow', $workflow)
            ->orderBy('e.started_at', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        $stats = [
            'total_executions' => count($executions),
            'successful' => count(array_filter($executions, fn($e) => $e->getStatus() === 'completed')),
            'failed' => count(array_filter($executions, fn($e) => $e->getStatus() === 'failed')),
            'running' => count(array_filter($executions, fn($e) => $e->getStatus() === 'running'))
        ];

        return View::render('workflows/show', [
            'title' => 'Workflow: ' . $workflow->getName(),
            'workflow' => $workflow,
            'executions' => $executions,
            'stats' => $stats
        ]);
    }

    public function edit($id)
    {
        $workflow = $this->entityManager->find(Workflow::class, $id);
        if (!$workflow) {
            header('Location: /workflows');
            exit;
        }

        $triggerTypes = [
            'customer_created' => 'Nový zákazník',
            'customer_updated' => 'Zákazník aktualizován',
            'deal_created' => 'Nový obchod',
            'deal_stage_changed' => 'Změna fáze obchodu',
            'deal_won' => 'Obchod vyhrán',
            'deal_lost' => 'Obchod prohrán',
            'contact_added' => 'Přidán kontakt',
            'invoice_created' => 'Vytvořena faktura',
            'invoice_paid' => 'Faktura zaplacena'
        ];

        $actionTypes = [
            'send_email' => 'Odeslat e-mail',
            'create_task' => 'Vytvořit úkol',
            'update_status' => 'Aktualizovat status',
            'add_note' => 'Přidat poznámku',
            'assign_user' => 'Přiřadit uživatele',
            'send_notification' => 'Odeslat notifikaci',
            'create_deal' => 'Vytvořit obchod',
            'update_customer' => 'Aktualizovat zákazníka'
        ];

        return View::render('workflows/edit', [
            'title' => 'Upravit workflow',
            'workflow' => $workflow,
            'triggerTypes' => $triggerTypes,
            'actionTypes' => $actionTypes
        ]);
    }

    public function update($id)
    {
        $workflow = $this->entityManager->find(Workflow::class, $id);
        if (!$workflow) {
            header('Location: /workflows');
            exit;
        }

        // $validator = new Validator();
        // $validator->required(['name', 'trigger_type'])
        //          ->maxLength('name', 255)
        //          ->maxLength('description', 1000);

        // if ($validator->fails()) {
        //     return View::render('workflows/edit', [
        //         'title' => 'Upravit workflow',
        //         'workflow' => $workflow,
        //         'errors' => $validator->errors(),
        //         'old' => $_POST
        //     ]);
        // }

        $workflow->setName($_POST['name'])
                ->setDescription($_POST['description'] ?? null)
                ->setTriggerType($_POST['trigger_type'])
                ->setTriggerConfig(json_decode($_POST['trigger_config'] ?? '{}', true))
                ->setConditions(json_decode($_POST['conditions'] ?? '[]', true))
                ->setActions(json_decode($_POST['actions'] ?? '[]', true))
                ->setIsActive(isset($_POST['is_active']))
                ->setPriority((int)($_POST['priority'] ?? 0));

        $this->entityManager->flush();

        header('Location: /workflows/' . $id);
        exit;
    }

    public function delete($id)
    {
        $workflow = $this->entityManager->find(Workflow::class, $id);
        if ($workflow) {
            $this->entityManager->remove($workflow);
            $this->entityManager->flush();
        }

        header('Location: /workflows');
        exit;
    }

    public function toggle($id)
    {
        $workflow = $this->entityManager->find(Workflow::class, $id);
        if ($workflow) {
            $workflow->setIsActive(!$workflow->isActive());
            $this->entityManager->flush();
        }

        header('Location: /workflows');
        exit;
    }

    public function test($id)
    {
        $workflow = $this->entityManager->find(Workflow::class, $id);
        if (!$workflow) {
            header('Location: /workflows');
            exit;
        }

        // Simulace spuštění workflow
        $execution = new WorkflowExecution();
        $execution->setWorkflow($workflow)
                 ->setStatus('running')
                 ->setTriggerData(['test' => true]);

        $this->entityManager->persist($execution);
        $this->entityManager->flush();

        // Zde by se spustil skutečný workflow engine
        $execution->setStatus('completed')
                 ->setCompletedAt(new \DateTime())
                 ->setExecutionTimeMs(rand(100, 1000))
                 ->addExecutionLog('test_execution', 'completed', 'Test workflow spuštěn úspěšně');

        $this->entityManager->flush();

        header('Location: /workflows/' . $id);
        exit;
    }

    private function getCurrentUser(): User
    {
        // Zde by se získal aktuální uživatel ze session
        return $this->entityManager->find(User::class, 1);
    }
}
