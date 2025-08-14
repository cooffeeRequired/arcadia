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
}
