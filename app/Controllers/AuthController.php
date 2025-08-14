<?php

namespace App\Controllers;

use App\Entities\User;
use Core\Notification\Notification;
use Core\Render\BaseController;
use Core\Traits\NotificationTrait;
use Core\Traits\Validation;
use DateTime;
use Doctrine\ORM\Exception\NotSupported;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Exception;
use JetBrains\PhpStorm\NoReturn;


class AuthController extends BaseController {
    use Validation, NotificationTrait;

    public function showLogin(): void
    {
        $this->renderView('auth.login');
    }

    #[NoReturn]
    public function logout(): void
    {
        $this->getRequest()->getSession()->destroy();
        $this->redirect('/login');
    }

    /**
     * @throws OptimisticLockException
     * @throws NotSupported
     * @throws ORMException|Exception
     */
    #[NoReturn]
    public function login(): void
    {
        $user = $this->em
            ->getRepository(User::class)
            ->findOneBy(['email' => $this->input('email')]);

        if (!$user || !password_verify($this->input('password'), $user->getPassword())) {
            Notification::error('Nesprávný email nebo heslo.');
            $this->redirect('/login');
        }

        if (!$user->isActive()) {
            Notification::error('Účet je deaktivován.');
            $this->redirect('/login');
        }

        $userData = $user->only(['id', 'name', 'email', 'role', 'avatar']);
        $this->session('user', $userData);
        $this->renderer->getSession()->saveCurrentSession();
        $user->setLastLogin(new DateTime());
        $this->em->flush();

        Notification::success('Přihlášení úspěšné!');
        $this->redirect('/');
    }
}
