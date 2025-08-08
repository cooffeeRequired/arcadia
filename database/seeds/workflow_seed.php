<?php



use App\Entities\Workflow;
use App\Entities\User;
use Core\Facades\Container;

$entityManager = Container::get('doctrine.em');

// Získat prvního uživatele
$user = $entityManager->getRepository(User::class)->findOneBy([]);
if (!$user) {
    echo "Žádný uživatel neexistuje. Vytvořte nejdříve uživatele.\n";
    exit;
}

// Vytvořit ukázkové workflow

// 1. Workflow pro nového zákazníka
$workflow1 = new Workflow();
$workflow1->setName('Uvítání nového zákazníka')
          ->setDescription('Automatické odeslání uvítacího e-mailu a vytvoření follow-up úkolu')
          ->setTriggerType('customer_created')
          ->setTriggerConfig([])
          ->setConditions([
              [
                  'type' => 'field_exists',
                  'field' => 'email',
                  'operator' => 'not_empty'
              ]
          ])
          ->setActions([
              [
                  'type' => 'send_email',
                  'template' => 'welcome_email',
                  'to' => '{{customer.email}}',
                  'subject' => 'Vítejte v našem CRM systému!'
              ],
              [
                  'type' => 'create_task',
                  'title' => 'Follow-up s novým zákazníkem',
                  'description' => 'Kontaktovat nového zákazníka {{customer.name}}',
                  'due_date' => '+3 days',
                  'priority' => 'medium'
              ]
          ])
          ->setIsActive(true)
          ->setPriority(10)
          ->setCreatedBy($user);

$entityManager->persist($workflow1);

// 2. Workflow pro vyhraný obchod
$workflow2 = new Workflow();
$workflow2->setName('Oslava vyhraného obchodu')
          ->setDescription('Automatické vytvoření faktury a notifikace týmu')
          ->setTriggerType('deal_won')
          ->setTriggerConfig([])
          ->setConditions([
              [
                  'type' => 'field_value',
                  'field' => 'value',
                  'operator' => 'greater_than',
                  'value' => 1000
              ]
          ])
          ->setActions([
              [
                  'type' => 'create_invoice',
                  'customer_id' => '{{deal.customer_id}}',
                  'amount' => '{{deal.value}}',
                  'description' => 'Faktura za obchod: {{deal.title}}'
              ],
              [
                  'type' => 'send_notification',
                  'to' => 'team',
                  'message' => 'Obchod vyhrán! {{deal.title}} - {{deal.value}} CZK'
              ],
              [
                  'type' => 'add_note',
                  'entity_type' => 'deal',
                  'entity_id' => '{{deal.id}}',
                  'note' => 'Obchod úspěšně uzavřen - automaticky vytvořena faktura'
              ]
          ])
          ->setIsActive(true)
          ->setPriority(8)
          ->setCreatedBy($user);

$entityManager->persist($workflow2);

// 3. Workflow pro prohraný obchod
$workflow3 = new Workflow();
$workflow3->setName('Analýza prohraného obchodu')
          ->setDescription('Vytvoření úkolu pro analýzu důvodů prohry')
          ->setTriggerType('deal_lost')
          ->setTriggerConfig([])
          ->setConditions([])
          ->setActions([
              [
                  'type' => 'create_task',
                  'title' => 'Analýza prohraného obchodu',
                  'description' => 'Analyzovat důvody prohry obchodu: {{deal.title}}',
                  'due_date' => '+1 week',
                  'priority' => 'high'
              ],
              [
                  'type' => 'send_email',
                  'template' => 'deal_lost_analysis',
                  'to' => '{{deal.assigned_user.email}}',
                  'subject' => 'Analýza prohraného obchodu'
              ]
          ])
          ->setIsActive(true)
          ->setPriority(5)
          ->setCreatedBy($user);

$entityManager->persist($workflow3);

// 4. Workflow pro změnu fáze obchodu
$workflow4 = new Workflow();
$workflow4->setName('Notifikace změny fáze obchodu')
          ->setDescription('Automatické notifikace při změně fáze obchodu')
          ->setTriggerType('deal_stage_changed')
          ->setTriggerConfig([])
          ->setConditions([
              [
                  'type' => 'field_value',
                  'field' => 'new_stage',
                  'operator' => 'in',
                  'value' => ['proposal', 'negotiation']
              ]
          ])
          ->setActions([
              [
                  'type' => 'send_notification',
                  'to' => '{{deal.assigned_user.id}}',
                  'message' => 'Obchod {{deal.title}} přešel do fáze: {{deal.stage}}'
              ],
              [
                  'type' => 'create_task',
                  'title' => 'Příprava na další fázi',
                  'description' => 'Připravit materiály pro fázi: {{deal.stage}}',
                  'due_date' => '+2 days',
                  'priority' => 'medium'
              ]
          ])
          ->setIsActive(true)
          ->setPriority(6)
          ->setCreatedBy($user);

$entityManager->persist($workflow4);

// 5. Workflow pro nezaplacené faktury
$workflow5 = new Workflow();
$workflow5->setName('Upomínka nezaplacené faktury')
          ->setDescription('Automatické odeslání upomínky po 30 dnech')
          ->setTriggerType('invoice_overdue')
          ->setTriggerConfig([
              'days_overdue' => 30
          ])
          ->setConditions([
              [
                  'type' => 'field_value',
                  'field' => 'status',
                  'operator' => 'equals',
                  'value' => 'unpaid'
              ]
          ])
          ->setActions([
              [
                  'type' => 'send_email',
                  'template' => 'invoice_reminder',
                  'to' => '{{invoice.customer.email}}',
                  'subject' => 'Upomínka: Nezaplacená faktura {{invoice.number}}'
              ],
              [
                  'type' => 'create_task',
                  'title' => 'Kontaktovat zákazníka ohledně nezaplacené faktury',
                  'description' => 'Faktura {{invoice.number}} je nezaplacená {{invoice.days_overdue}} dní',
                  'due_date' => '+1 day',
                  'priority' => 'high'
              ]
          ])
          ->setIsActive(true)
          ->setPriority(7)
          ->setCreatedBy($user);

$entityManager->persist($workflow5);

$entityManager->flush();

echo "Vytvořeno 5 ukázkových workflow:\n";
echo "- Uvítání nového zákazníka\n";
echo "- Oslava vyhraného obchodu\n";
echo "- Analýza prohraného obchodu\n";
echo "- Notifikace změny fáze obchodu\n";
echo "- Upomínka nezaplacené faktury\n";
