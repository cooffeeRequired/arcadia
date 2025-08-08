<?php

use App\Entities\User;
use Core\Facades\Container;

$entityManager = Container::get('doctrine.em');

// Kontrola, jestli uživatelé už existují
$existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => 'admin@arcadia-crm.com']);
if ($existingUser) {
    echo "Uživatelé už existují, přeskakuji vytváření.\n";
    return;
}

// Vytvořit admin uživatele
$admin = new User();
$admin->setName('Admin User')
      ->setEmail('admin@arcadia-crm.com')
      ->setPassword(password_hash('admin123', PASSWORD_DEFAULT))
      ->setRole('admin');

$entityManager->persist($admin);

// Vytvořit manager uživatele
$manager = new User();
$manager->setName('Manager User')
        ->setEmail('manager@arcadia-crm.com')
        ->setPassword(password_hash('manager123', PASSWORD_DEFAULT))
        ->setRole('manager');

$entityManager->persist($manager);

// Vytvořit běžného uživatele
$user = new User();
$user->setName('Regular User')
     ->setEmail('user@arcadia-crm.com')
     ->setPassword(password_hash('user123', PASSWORD_DEFAULT))
     ->setRole('user');

$entityManager->persist($user);

$entityManager->flush();

echo "Vytvořeno 3 uživatelé:\n";
echo "- Admin (admin@arcadia-crm.com / admin123)\n";
echo "- Manager (manager@arcadia-crm.com / manager123)\n";
echo "- User (user@arcadia-crm.com / user123)\n";
