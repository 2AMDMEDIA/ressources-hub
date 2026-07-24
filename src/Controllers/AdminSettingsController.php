<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Csrf;
use App\Middleware\Auth;
use App\Repositories\UserRepository;
use App\Session;

/**
 * Back-office super-admin — Paramètres : gestion des comptes super-admin.
 */
final class AdminSettingsController extends BaseController
{
    public function index(): void
    {
        Auth::requireSuperAdmin();
        $this->render('pages.admin.settings.index', layout: 'layouts.admin', data: [
            'title' => 'Paramètres',
            'super_admins' => (new UserRepository())->listSuperAdmins(),
            'current_user_id' => Session::userId(),
            'admin' => ['active' => 'settings', 'page_title' => 'Paramètres', 'user_name' => (string) Session::get('user_full_name', '')],
        ]);
    }

    public function createSuperAdmin(): void
    {
        Auth::requireSuperAdmin();
        Csrf::enforce($this->input('_csrf'));

        $first = $this->input('first_name');
        $last = $this->input('last_name');
        $email = $this->input('email');
        $password = $this->input('password');

        if ($first === null || $last === null) {
            $this->flashError('Prénom et nom requis.');
            $this->redirect('/admin/settings');
        }
        if ($email === null || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->flashError('Email valide requis.');
            $this->redirect('/admin/settings');
        }
        if ($password === null || strlen($password) < 8) {
            $this->flashError('Mot de passe trop court (8 caractères minimum).');
            $this->redirect('/admin/settings');
        }

        $users = new UserRepository();
        if ($users->findByEmail($email) !== null) {
            $this->flashError('Un compte existe déjà avec cet email.');
            $this->redirect('/admin/settings');
        }

        $users->create(
            email: (string) $email,
            plainPassword: (string) $password,
            role: 'super_admin',
            clubId: null,
            needsPasswordSetup: false,
            firstName: $first,
            lastName: $last,
        );

        $this->flashSuccess('Compte super-admin créé.');
        $this->redirect('/admin/settings');
    }

    public function removeSuperAdmin(string $id): void
    {
        Auth::requireSuperAdmin();
        Csrf::enforce($this->input('_csrf'));

        $users = new UserRepository();
        if ($id === Session::userId()) {
            $this->flashError('Vous ne pouvez pas retirer votre propre compte.');
            $this->redirect('/admin/settings');
        }
        if ($users->countSuperAdmins() <= 1) {
            $this->flashError('Il doit rester au moins un super-admin.');
            $this->redirect('/admin/settings');
        }
        $target = $users->findById($id);
        if ($target === null || !$target->isSuperAdmin) {
            $this->flashError('Compte introuvable.');
            $this->redirect('/admin/settings');
        }

        $users->deleteById($id);
        $this->flashSuccess('Compte super-admin supprimé.');
        $this->redirect('/admin/settings');
    }
}
