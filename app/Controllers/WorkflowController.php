<?php

namespace App\Controllers;

use App\Entities\Workflow;
use App\Entities\WorkflowExecution;
use App\Entities\User;
use Core\Http\Response;
use Core\Render\BaseController;

class WorkflowController extends BaseController
{
    public function index(): Response\ViewResponse
    {
        $workflows = $this->em->getRepository(Workflow::class)
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

        return $this->view('workflows/index', [
            'title' => 'Workflow',
            'workflows' => $workflows,
            'stats' => $stats
        ]);
    }

    public function create(): Response\ViewResponse
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

        return $this->view('workflows/create', [
            'title' => 'Vytvořit workflow',
            'triggerTypes' => $triggerTypes,
            'actionTypes' => $actionTypes
        ]);
    }

    public function store(): void
    {
        $workflow = new Workflow();
        $workflow->setName($this->input('name'))
                ->setDescription($this->input('description', null))
                ->setTriggerType($this->input('trigger_type'))
                ->setTriggerConfig(json_decode($this->input('trigger_config', '{}'), true))
                ->setConditions(json_decode($this->input('conditions', '[]'), true))
                ->setActions(json_decode($this->input('actions', '[]'), true))
                ->setIsActive($this->has('is_active'))
                ->setPriority((int)($this->input('priority', 0)))
                ->setCreatedBy($this->getCurrentUser());

        $this->em->persist($workflow);
        $this->em->flush();

        $this->redirect('/workflows');
    }

    public function show($id): Response\ViewResponse
    {
        $workflow = $this->em->find(Workflow::class, $id);
        if (!$workflow) {
            $this->redirect('/workflows');
        }

        // Získat poslední spuštění
        $executions = $this->em->getRepository(WorkflowExecution::class)
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

        return $this->view('workflows/show', [
            'title' => 'Workflow: ' . $workflow->getName(),
            'workflow' => $workflow,
            'executions' => $executions,
            'stats' => $stats
        ]);
    }

    public function edit($id): Response\ViewResponse
    {
        $workflow = $this->em->find(Workflow::class, $id);
        if (!$workflow) {
            $this->redirect('/workflows');
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

        return $this->view('workflows/edit', [
            'title' => 'Upravit workflow',
            'workflow' => $workflow,
            'triggerTypes' => $triggerTypes,
            'actionTypes' => $actionTypes
        ]);
    }

    public function update($id): void
    {
        $workflow = $this->em->find(Workflow::class, $id);
        if (!$workflow) {
            $this->redirect('/workflows');
        }

        $workflow->setName($this->input('name'))
                ->setDescription($this->input('description', null))
                ->setTriggerType($this->input('trigger_type'))
                ->setTriggerConfig(json_decode($this->input('trigger_config', '{}'), true))
                ->setConditions(json_decode($this->input('conditions', '[]'), true))
                ->setActions(json_decode($this->input('actions', '[]'), true))
                ->setIsActive($this->has('is_active'))
                ->setPriority((int)($this->input('priority', 0)));

        $this->em->flush();

        $this->redirect('/workflows/' . $id);
    }

    public function delete($id): void
    {
        $workflow = $this->em->find(Workflow::class, $id);
        if ($workflow) {
            $this->em->remove($workflow);
            $this->em->flush();
        }

        $this->redirect('/workflows');
    }

    public function toggle($id): void
    {
        $workflow = $this->em->find(Workflow::class, $id);
        if ($workflow) {
            $workflow->setIsActive(!$workflow->isActive());
            $this->em->flush();
        }

        $this->redirect('/workflows');
    }

    public function test($id): void
    {
        $workflow = $this->em->find(Workflow::class, $id);
        if (!$workflow) {
            $this->redirect('/workflows');
        }

        // Simulace spuštění workflow
        $execution = new WorkflowExecution();
        $execution->setWorkflow($workflow)
                 ->setStatus('running')
                 ->setTriggerData(['test' => true]);

        $this->em->persist($execution);
        $this->em->flush();

        // Zde by se spustil skutečný workflow engine
        $execution->setStatus('completed')
                 ->setCompletedAt(new \DateTime())
                 ->setExecutionTimeMs(rand(100, 1000))
                 ->addExecutionLog('test_execution', 'completed', 'Test workflow spuštěn úspěšně');

        $this->em->flush();

        $this->redirect('/workflows/' . $id);
    }

    private function getCurrentUser(): User
    {
        // Zde by se získal aktuální uživatel ze session
        return $this->em->find(User::class, 1);
    }
}
