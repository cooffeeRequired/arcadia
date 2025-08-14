<?php

namespace App\Controllers;

use App\Entities\User;
use Core\Http\Response;
use Core\Render\BaseController;
use Core\Traits\Validation;
use DateTime;
use Doctrine\ORM\Exception\NotSupported;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Exception;

class AuthController extends BaseController {
    use Validation;

    public function showLogin(): Response\ViewResponse
    {
        return $this->view('auth.login');
    }

    public function showRegister(): Response\ViewResponse
    {
        return $this->view('auth.register');
    }

    public function logout(): void
    {
        $this->request->getSession()->destroy();
        $this->redirect('/login');
    }

    /**
     * @throws OptimisticLockException
     * @throws NotSupported
     * @throws ORMException|Exception
     */
    public function login(): void
    {
        $user = $this->em
            ->getRepository(User::class)
            ->findOneBy(['email' => $this->input('email')]);

        if (!$user || !password_verify($this->input('password'), $user->getPassword())) {
            $this->toastError('Nesprávný email nebo heslo.');
            $this->redirect('/login');
        }

        if (!$user->isActive()) {
            $this->toastError('Účet je deaktivován.');
            $this->redirect('/login');
        }

        $userData = $user->only(['id', 'name', 'email', 'role', 'avatar']);
        $this->session('user', $userData);
        $this->request->getSession()->saveCurrentSession();
        $user->setLastLogin(new DateTime());
        $this->em->flush();

        $this->toastSuccess('Přihlášení úspěšné!');
        $this->redirect('/');
    }

    /**
     * @throws OptimisticLockException
     * @throws NotSupported
     * @throws ORMException|Exception
     */
    public function register(): void
    {
        // Kontrola, zda uživatel s tímto emailem již neexistuje
        $existingUser = $this->em
            ->getRepository(User::class)
            ->findOneBy(['email' => $this->input('email')]);

        if ($existingUser) {
            $this->toastError('Uživatel s tímto emailem již existuje.');
            $this->redirect('/register');
            return;
        }

        // Vytvoření nového uživatele
        $user = new User();
        $user->setName($this->input('name'));
        $user->setEmail($this->input('email'));
        $user->setPassword(password_hash($this->input('password'), PASSWORD_DEFAULT));
        $user->setRole('user');
        $user->setIsActive(true);

        $this->em->persist($user);
        $this->em->flush();

        $this->toastSuccess('Registrace úspěšná! Nyní se můžete přihlásit.');
        $this->redirect('/login');
    }
}
