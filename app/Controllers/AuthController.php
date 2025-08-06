<?php

namespace App\Controllers;

use App\Entities\User;
use App\Traits\Validation;
use Core\Facades\Container;
use Core\Notification\Notification;
use Core\Traits\Controller;
use Core\Traits\NotificationTrait;
use DateTime;
use Doctrine\ORM\Exception\NotSupported;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Exception;
use Illuminate\Validation\ValidationException;
use JetBrains\PhpStorm\NoReturn;

class AuthController
{
    use Controller, Validation, NotificationTrait;

    private mixed $em;

    public function __construct()
    {
        $this->em = Container::get('doctrine.em');
    }

    public function showLogin(): void
    {
        $this->middleware('guest');
        $this->renderView('auth.login', ['renderer' => $this->renderer]);
    }

    /**
     * @throws OptimisticLockException
     * @throws NotSupported
     * @throws ORMException|Exception
     */
    #[NoReturn] public function login(): void
    {
        try {
            $validatedData = $this->validateData(
                $_POST,
                [
                    'email' => 'required|email',
                    'password' => 'required|min:1'
                ],
                [
                    'email.required' => 'Email je povinný.',
                    'email.email' => 'Zadejte platný email.',
                    'password.required' => 'Heslo je povinné.'
                ]
            );

            $email = $validatedData['email'];
            $password = $validatedData['password'];
        } catch (ValidationException $e) {
            $this->session('errors', $e->errors());
            $this->redirect('/login');
        }

        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user || !password_verify($password, $user->getPassword())) {
            //$this->redirect('/login');
            Notification::error('Nesprávny email nebo heslo.');
            return;
        }

        if (!$user->isActive()) {
            $this->session('error', 'Účet je deaktivován.');
            $this->redirect('/login');
        }

        $this->session('user', $user);

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
        try {
            $validatedData = $this->validateData(
                $_POST,
                [
                    'name' => 'required|min:2|max:50',
                    'email' => 'required|email|max:255',
                    'password' => 'required|min:6|max:255',
                    'password_confirm' => 'required|same:password'
                ],
                [
                    'name.required' => 'Jméno je povinné.',
                    'name.min' => 'Jméno musí mít alespoň 2 znaky.',
                    'name.max' => 'Jméno může mít maximálně 50 znaků.',
                    'email.required' => 'Email je povinný.',
                    'email.email' => 'Zadejte platný email.',
                    'email.max' => 'Email může mít maximálně 255 znaků.',
                    'password.required' => 'Heslo je povinné.',
                    'password.min' => 'Heslo musí mít alespoň 6 znaků.',
                    'password.max' => 'Heslo může mít maximálně 255 znaků.',
                    'password_confirm.required' => 'Potvrzení hesla je povinné.',
                    'password_confirm.same' => 'Hesla se neshodují.'
                ]
            );

            $name = $validatedData['name'];
            $email = $validatedData['email'];
            $password = $validatedData['password'];
        } catch (ValidationException $e) {
            $this->session('errors', $e->errors());
            $this->redirect('/register');
        }

        // Kontrola, zda uživatel již existuje
        $existingUser = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($existingUser) {
            $this->addValidationError('email', 'Uživatel s tímto emailem již existuje.');
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
        $this->middleware('auth');
        session_destroy();
        $this->redirect('/login');
    }
} 