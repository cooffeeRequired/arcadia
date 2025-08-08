<?php

namespace Core\Workflow;

use App\Entities\Workflow;
use App\Entities\WorkflowExecution;
use App\Entities\Customer;
use App\Entities\Deal;
use App\Entities\Contact;
use App\Entities\Invoice;
use Core\Database\Database;

class WorkflowEngine
{
    private $entityManager;

    public function __construct()
    {
        $this->entityManager = Database::getEntityManager();
    }

    /**
     * Spustí workflow pro daný trigger
     */
    public function trigger(string $triggerType, array $data = []): array
    {
        $workflows = $this->entityManager->getRepository(Workflow::class)
            ->createQueryBuilder('w')
            ->where('w.trigger_type = :trigger_type')
            ->andWhere('w.is_active = :is_active')
            ->setParameter('trigger_type', $triggerType)
            ->setParameter('is_active', true)
            ->orderBy('w.priority', 'DESC')
            ->getQuery()
            ->getResult();

        $results = [];
        
        foreach ($workflows as $workflow) {
            try {
                $execution = $this->executeWorkflow($workflow, $data);
                $results[] = $execution;
            } catch (\Exception $e) {
                // Log error
                error_log("Workflow execution failed: " . $e->getMessage());
                $results[] = [
                    'workflow_id' => $workflow->getId(),
                    'status' => 'failed',
                    'error' => $e->getMessage()
                ];
            }
        }

        return $results;
    }

    /**
     * Spustí konkrétní workflow
     */
    public function executeWorkflow(Workflow $workflow, array $data = []): array
    {
        $startTime = microtime(true);
        
        // Vytvořit execution record
        $execution = new WorkflowExecution();
        $execution->setWorkflow($workflow)
                 ->setStatus('running')
                 ->setTriggerData($data);

        $this->entityManager->persist($execution);
        $this->entityManager->flush();

        try {
            // Kontrola podmínek
            if (!$this->checkConditions($workflow->getConditions(), $data)) {
                $execution->setStatus('cancelled')
                         ->setCompletedAt(new \DateTime())
                         ->setExecutionTimeMs((int)((microtime(true) - $startTime) * 1000))
                         ->addExecutionLog('conditions_check', 'cancelled', 'Podmínky nebyly splněny');
                
                $this->entityManager->flush();
                return [
                    'workflow_id' => $workflow->getId(),
                    'status' => 'cancelled',
                    'reason' => 'conditions_not_met'
                ];
            }

            // Spuštění akcí
            $actions = $workflow->getActions();
            foreach ($actions as $action) {
                $this->executeAction($action, $data, $execution);
            }

            $execution->setStatus('completed')
                     ->setCompletedAt(new \DateTime())
                     ->setExecutionTimeMs((int)((microtime(true) - $startTime) * 1000))
                     ->addExecutionLog('workflow_completed', 'completed', 'Workflow úspěšně dokončen');

            $this->entityManager->flush();

            return [
                'workflow_id' => $workflow->getId(),
                'status' => 'completed',
                'execution_id' => $execution->getId()
            ];

        } catch (\Exception $e) {
            $execution->setStatus('failed')
                     ->setCompletedAt(new \DateTime())
                     ->setExecutionTimeMs((int)((microtime(true) - $startTime) * 1000))
                     ->setErrorMessage($e->getMessage())
                     ->addExecutionLog('workflow_failed', 'failed', $e->getMessage());

            $this->entityManager->flush();

            throw $e;
        }
    }

    /**
     * Kontrola podmínek workflow
     */
    private function checkConditions(array $conditions, array $data): bool
    {
        if (empty($conditions)) {
            return true; // Žádné podmínky = vždy splněno
        }

        foreach ($conditions as $condition) {
            if (!$this->evaluateCondition($condition, $data)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Vyhodnocení jedné podmínky
     */
    private function evaluateCondition(array $condition, array $data): bool
    {
        $type = $condition['type'] ?? '';
        $field = $condition['field'] ?? '';
        $operator = $condition['operator'] ?? '';
        $value = $condition['value'] ?? '';

        $fieldValue = $this->getFieldValue($field, $data);

        switch ($type) {
            case 'field_exists':
                return isset($data[$field]);
                
            case 'field_empty':
                return empty($fieldValue);
                
            case 'field_not_empty':
                return !empty($fieldValue);
                
            case 'field_value':
                return $this->compareValues($fieldValue, $operator, $value);
                
            default:
                return true;
        }
    }

    /**
     * Získá hodnotu pole z dat
     */
    private function getFieldValue(string $field, array $data)
    {
        // Podpora pro nested fields (např. customer.email)
        $parts = explode('.', $field);
        $value = $data;

        foreach ($parts as $part) {
            if (isset($value[$part])) {
                $value = $value[$part];
            } else {
                return null;
            }
        }

        return $value;
    }

    /**
     * Porovná hodnoty podle operátoru
     */
    private function compareValues($fieldValue, string $operator, $value): bool
    {
        switch ($operator) {
            case 'equals':
                return $fieldValue == $value;
                
            case 'not_equals':
                return $fieldValue != $value;
                
            case 'contains':
                return strpos($fieldValue, $value) !== false;
                
            case 'greater_than':
                return $fieldValue > $value;
                
            case 'less_than':
                return $fieldValue < $value;
                
            case 'in':
                return in_array($fieldValue, (array)$value);
                
            default:
                return false;
        }
    }

    /**
     * Spustí akci
     */
    private function executeAction(array $action, array $data, WorkflowExecution $execution): void
    {
        $type = $action['type'] ?? '';
        $param1 = $action['param1'] ?? '';
        $param2 = $action['param2'] ?? '';

        // Nahrazení placeholderů v parametrech
        $param1 = $this->replacePlaceholders($param1, $data);
        $param2 = $this->replacePlaceholders($param2, $data);

        try {
            switch ($type) {
                case 'send_email':
                    $this->sendEmail($param1, $param2, $data);
                    break;
                    
                case 'create_task':
                    $this->createTask($param1, $param2, $data);
                    break;
                    
                case 'update_status':
                    $this->updateStatus($param1, $param2, $data);
                    break;
                    
                case 'add_note':
                    $this->addNote($param1, $param2, $data);
                    break;
                    
                case 'assign_user':
                    $this->assignUser($param1, $param2, $data);
                    break;
                    
                case 'send_notification':
                    $this->sendNotification($param1, $param2, $data);
                    break;
                    
                case 'create_deal':
                    $this->createDeal($param1, $param2, $data);
                    break;
                    
                case 'update_customer':
                    $this->updateCustomer($param1, $param2, $data);
                    break;
                    
                default:
                    throw new \Exception("Neznámý typ akce: $type");
            }

            $execution->addExecutionLog($type, 'completed', "Akce $type úspěšně provedena");

        } catch (\Exception $e) {
            $execution->addExecutionLog($type, 'failed', $e->getMessage());
            throw $e;
        }
    }

    /**
     * Nahradí placeholdery v textu
     */
    private function replacePlaceholders(string $text, array $data): string
    {
        return preg_replace_callback('/\{\{([^}]+)\}\}/', function($matches) use ($data) {
            $field = trim($matches[1]);
            $value = $this->getFieldValue($field, $data);
            return $value ?? '';
        }, $text);
    }

    // Implementace jednotlivých akcí
    private function sendEmail(string $to, string $subject, array $data): void
    {
        // Implementace odeslání e-mailu
        // Zde by byla integrace s e-mailovým systémem
        error_log("Sending email to: $to, subject: $subject");
    }

    private function createTask(string $title, string $description, array $data): void
    {
        // Implementace vytvoření úkolu
        // Zde by byla integrace s task systémem
        error_log("Creating task: $title - $description");
    }

    private function updateStatus(string $entity, string $status, array $data): void
    {
        // Implementace změny statusu
        error_log("Updating status for $entity to $status");
    }

    private function addNote(string $entityType, string $note, array $data): void
    {
        // Implementace přidání poznámky
        error_log("Adding note to $entityType: $note");
    }

    private function assignUser(string $entity, string $userId, array $data): void
    {
        // Implementace přiřazení uživatele
        error_log("Assigning user $userId to $entity");
    }

    private function sendNotification(string $to, string $message, array $data): void
    {
        // Implementace odeslání notifikace
        error_log("Sending notification to $to: $message");
    }

    private function createDeal(string $title, string $description, array $data): void
    {
        // Implementace vytvoření obchodu
        error_log("Creating deal: $title - $description");
    }

    private function updateCustomer(string $field, string $value, array $data): void
    {
        // Implementace aktualizace zákazníka
        error_log("Updating customer field $field to $value");
    }
}
