<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Csrf;
use App\Middleware\Auth;
use App\Repositories\UserRepository;

/**
 * Actions admin transverses sur les comptes utilisateurs (users) :
 * définir/réinitialiser le mot de passe de n'importe quel compte.
 */
final class AdminUsersController extends BaseController
{
    public function setPassword(string $id): void
    {
        Auth::requireSuperAdmin();
        Csrf::enforce($this->input('_csrf'));

        $repo = new UserRepository();
        $user = $repo->findById($id);
        if ($user === null) {
            $this->flashError('Compte introuvable.');
            $this->back();
        }

        $password = $this->input('password');
        if ($password === null || strlen($password) < 8) {
            $this->flashError('Le mot de passe doit contenir au moins 8 caractères.');
            $this->back();
        }

        $repo->updatePassword($id, (string) $password);
        $this->flashSuccess('Mot de passe mis à jour pour ' . $user->displayName() . '.');
        $this->back();
    }
}
