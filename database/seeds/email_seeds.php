<?php

require_once __DIR__ . '/../../bootstrap.php';

use App\Entities\EmailSignature;
use App\Entities\EmailServer;
use App\Entities\EmailTemplate;
use App\Entities\User;
use Core\Facades\Container;
use Doctrine\ORM\EntityManager;

$em = Container::get('doctrine.em');

// Získání prvního uživatele pro seed data
$user = $em->getRepository(User::class)->findOneBy([]);

if (!$user) {
    echo "Žádný uživatel nebyl nalezen. Nejprve vytvořte uživatele.\n";
    return;
}

// Seed data pro e-mailové podpisy
$signatures = [
    [
        'name' => 'Standardní podpis',
        'content' => '<p>Děkuji za Vaši důvěru.</p><p>S pozdravem,<br>Arcadia CRM Team</p>',
        'is_default' => true
    ],
    [
        'name' => 'Formální podpis',
        'content' => '<p>V případě jakýchkoliv dotazů nás neváhejte kontaktovat.</p><p>S úctou,<br>Arcadia CRM</p>',
        'is_default' => false
    ]
];

foreach ($signatures as $signatureData) {
    $signature = new EmailSignature();
    $signature->setName($signatureData['name']);
    $signature->setContent($signatureData['content']);
    $signature->setIsDefault($signatureData['is_default']);
    $signature->setUser($user);

    $em->persist($signature);
}

// Seed data pro e-mailové servery
$servers = [
    [
        'name' => 'Gmail SMTP',
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'encryption' => 'tls',
        'username' => 'your-email@gmail.com',
        'password' => 'your-app-password',
        'from_email' => 'your-email@gmail.com',
        'from_name' => 'Arcadia CRM',
        'is_active' => true,
        'is_default' => true
    ],
    [
        'name' => 'Outlook SMTP',
        'host' => 'smtp-mail.outlook.com',
        'port' => 587,
        'encryption' => 'tls',
        'username' => 'your-email@outlook.com',
        'password' => 'your-password',
        'from_email' => 'your-email@outlook.com',
        'from_name' => 'Arcadia CRM',
        'is_active' => true,
        'is_default' => false
    ]
];

foreach ($servers as $serverData) {
    $server = new EmailServer();
    $server->setName($serverData['name']);
    $server->setHost($serverData['host']);
    $server->setPort($serverData['port']);
    $server->setEncryption($serverData['encryption']);
    $server->setUsername($serverData['username']);
    $server->setPassword($serverData['password']);
    $server->setFromEmail($serverData['from_email']);
    $server->setFromName($serverData['from_name']);
    $server->setIsActive($serverData['is_active']);
    $server->setIsDefault($serverData['is_default']);
    $server->setUser($user);

    $em->persist($server);
}

// Seed data pro e-mailové šablony
$templates = [
    [
        'name' => 'Uvítací e-mail',
        'subject' => 'Vítejte v Arcadia CRM',
        'content' => '<h2>Vítejte v Arcadia CRM!</h2><p>Děkujeme za registraci do našeho CRM systému.</p><p>Váš tým Arcadia CRM</p>',
        'category' => 'welcome',
        'is_active' => true
    ],
    [
        'name' => 'Follow-up e-mail',
        'subject' => 'Následný kontakt',
        'content' => '<h2>Následný kontakt</h2><p>Rádi bychom se zeptali, jak se Vám líbí naše služby.</p><p>S pozdravem,<br>Arcadia CRM</p>',
        'category' => 'follow_up',
        'is_active' => true
    ],
    [
        'name' => 'Faktura',
        'subject' => 'Faktura č. {invoice_number}',
        'content' => '<h2>Faktura č. {invoice_number}</h2><p>V příloze naleznete fakturu za naše služby.</p><p>Děkujeme za Vaši důvěru.</p>',
        'category' => 'invoice',
        'is_active' => true
    ],
    [
        'name' => 'Připomenutí',
        'subject' => 'Připomenutí: {reminder_text}',
        'content' => '<h2>Připomenutí</h2><p>{reminder_text}</p><p>S pozdravem,<br>Arcadia CRM</p>',
        'category' => 'reminder',
        'is_active' => true
    ]
];

foreach ($templates as $templateData) {
    $template = new EmailTemplate();
    $template->setName($templateData['name']);
    $template->setSubject($templateData['subject']);
    $template->setContent($templateData['content']);
    $template->setCategory($templateData['category']);
    $template->setIsActive($templateData['is_active']);
    $template->setUser($user);

    $em->persist($template);
}

$em->flush();

echo "E-mailový modul seed data byla úspěšně vytvořena.\n";
