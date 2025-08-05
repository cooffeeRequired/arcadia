<?php

namespace App\Controllers;

use App\Entities\User;
use Core\Facades\Container;
use Core\Routing\Middleware;
use Core\Traits\Controller;
use DateTime;
use Doctrine\ORM\Exception\NotSupported;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use JetBrains\PhpStorm\NoReturn;

class AuthController
{
    use Controller;

    private mixed $em;

    public function __construct()
    {
        $this->em = Container::get('doctrine.em');
    }

    public function showLogin(): string
    {
        $this->middleware('guest');
        return $this->view('auth.login');
    }

    /**
     * @throws OptimisticLockException
     * @throws NotSupported
     * @throws ORMException
     */
    #[NoReturn] public function login(): void
    {
        $email = $this->input('email', '');
        $password = $this->input('password', '');

        if (empty($email) || empty($password)) {
            $this->session('error', 'Vyplňte prosím všechna pole.');
            $this->redirect('/login');
        }

        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user || !password_verify($password, $user->getPassword())) {
            $this->session('error', 'Nesprávny email nebo heslo.');
            $this->redirect('/login');
        }

        if (!$user->isActive()) {
            $this->session('error', 'Účet je deaktivován.');
            $this->redirect('/login');
        }

        $this->session('user_id', $user->getId());
        $this->session('user_name', $user->getName());
        $this->session('user_email', $user->getEmail());
        $this->session('user_role', $user->getRole());
        $user->setLastLogin(new DateTime());
        $this->em->flush();

        $this->session('success', 'Přihlášení úspěšné!');
        $this->redirect('/');
    }

    public function showRegister(): string
    {
        $this->guest();
        return $this->view('auth.register');
    }

    #[NoReturn] public function register()
    {
        $name = $this->input('name', '');
        $email = $this->input('email', '');
        $password = $this->input('password', '');
        $password_confirm = $this->input('password_confirm', '');

        // Validace
        if (empty($name) || empty($email) || empty($password) || empty($password_confirm)) {
            $this->session('error', 'Vyplňte prosím všechna pole.');
            $this->redirect('/register');
        }

        if ($password !== $password_confirm) {
            $this->session('error', 'Hesla se neshodují.');
            $this->redirect('/register');
        }

        if (strlen($password) < 6) {
            $this->session('error', 'Heslo musí mít alespoň 6 znaků.');
            $this->redirect('/register');
        }

        // Kontrola, zda uživatel již existuje
        $existingUser = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($existingUser) {
            $this->session('error', 'Uživatel s tímto emailem již existuje.');
            $this->redirect('/register');
        }

        // Vytvoření nového uživatele
        $user = new User();
        $user->setName($name)
             ->setEmail($email)
             ->setPassword(password_hash($password, PASSWORD_DEFAULT))
             ->setRole('user');

        $this->em->persist($user);
        $this->em->flush();

        $this->session('success', 'Registrace úspěšná! Nyní se můžete přihlásit.');
        $this->redirect('/login');
    }

    #[NoReturn] public function logout()
    {
        session_destroy();
        $this->redirect('/login');
    }
} 