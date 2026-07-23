<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Csrf;
use App\Middleware\Membership;
use App\Repositories\UserRepository;
use App\Session;

/**
 * Espace membre — « Mon compte ».
 * Le manager (ou tout membre) modifie ses propres informations d'identité.
 * Il ne peut PAS modifier son club (lecture seule).
 */
final class AccountController extends BaseController
{
    public function show(): void
    {
        Membership::guard();
        $user = Membership::currentUser();
        $club = Membership::currentClub();

        $this->renderApp('pages.account.index', [
            'title' => 'Mon compte',
            'user' => $user,
            'club' => $club,
        ], ['active' => 'account', 'page_title' => 'Mon compte']);
    }

    public function update(): void
    {
        Membership::guard();
        Csrf::enforce($this->input('_csrf'));
        $user = Membership::currentUser();
        if ($user === null) {
            $this->redirect('/login');
        }

        $first = $this->input('first_name');
        $last = $this->input('last_name');
        $email = $this->input('email');
        $job = $this->input('job_title');

        if ($first === null || $last === null || $email === null || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->flashError('Prénom, nom et email valide sont requis.');
            $this->redirect('/compte');
        }

        $users = new UserRepository();
        $other = $users->findByEmail($email);
        if ($other !== null && $other->id !== $user->id) {
            $this->flashError('Cet email est déjà utilisé par un autre compte.');
            $this->redirect('/compte');
        }

        $users->updateProfile($user->id, (string) $first, (string) $last, (string) $email, $job);

        // Rafraîchir la session (nom affiché dans l'en-tête).
        Session::set('user_full_name', trim($first . ' ' . $last));
        Session::set('user_email', mb_strtolower((string) $email));

        $this->flashSuccess('Vos informations ont été mises à jour.');
        $this->redirect('/compte');
    }
}
