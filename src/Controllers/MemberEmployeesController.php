<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Bootstrap;
use App\Helpers\Csrf;
use App\Middleware\Membership;
use App\Models\Club;
use App\Models\User;
use App\Repositories\EmployeeRepository;
use App\Repositories\PasswordTokenRepository;
use App\Repositories\UserRepository;
use App\Services\Mailer;

/**
 * Espace membre — gestion des employés de SON club par le manager.
 * Réservé au manager (club_owner) : un simple employé (club_member) n'y accède pas.
 */
final class MemberEmployeesController extends BaseController
{
    public function index(): void
    {
        $club = Membership::guard();
        $this->requireManager();

        $employees = $club !== null ? (new EmployeeRepository())->listByClub($club->id) : [];
        $this->renderApp('pages.member.employees', [
            'title' => 'Employés',
            'club' => $club,
            'employees' => $employees,
        ], ['active' => 'employees', 'page_title' => 'Employés']);
    }

    public function store(): void
    {
        $club = Membership::guard();
        $this->requireManager();
        Csrf::enforce($this->input('_csrf'));
        if ($club === null) {
            $this->flashError('Aucun club rattaché à votre compte.');
            $this->redirect('/employes');
        }

        $first = $this->input('first_name');
        $last = $this->input('last_name');
        if ($first === null || $last === null) {
            $this->flashError('Le prénom et le nom sont requis.');
            $this->redirect('/employes');
        }
        $email = $this->input('email');
        if ($email !== null && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->flashError('Email invalide.');
            $this->redirect('/employes');
        }

        (new EmployeeRepository())->create($club->id, (string) $first, (string) $last, $email, $this->input('job_title'));
        $this->flashSuccess('Employé ajouté.');
        $this->redirect('/employes');
    }

    /** Donne un accès de connexion à un employé (crée un compte membre + invitation). */
    public function grantAccess(string $id): void
    {
        $club = Membership::guard();
        $this->requireManager();
        Csrf::enforce($this->input('_csrf'));

        $repo = new EmployeeRepository();
        $emp = $repo->findById($id);
        if ($club === null || $emp === null || $emp->clubId !== $club->id) {
            $this->flashError('Action non autorisée.');
            $this->redirect('/employes');
        }
        if ($emp->hasAccess()) {
            $this->flashError('Cet employé a déjà un accès.');
            $this->redirect('/employes');
        }
        if ($emp->email === null || !filter_var($emp->email, FILTER_VALIDATE_EMAIL)) {
            $this->flashError('Renseignez d\'abord un email valide pour cet employé.');
            $this->redirect('/employes');
        }

        $users = new UserRepository();
        if ($users->findByEmail($emp->email) !== null) {
            $this->flashError('Un compte existe déjà avec cet email.');
            $this->redirect('/employes');
        }

        $user = $users->create(
            email: $emp->email,
            plainPassword: null,
            role: 'club_member',
            clubId: $club->id,
            needsPasswordSetup: true,
            firstName: $emp->firstName,
            lastName: $emp->lastName,
            jobTitle: $emp->jobTitle,
        );
        $repo->setUserId($emp->id, $user->id);
        $this->sendInvitation($user, $club);

        $this->flashSuccess('Accès envoyé à ' . $emp->email . ' (invitation par email).');
        $this->redirect('/employes');
    }

    public function delete(string $id): void
    {
        $club = Membership::guard();
        $this->requireManager();
        Csrf::enforce($this->input('_csrf'));

        $repo = new EmployeeRepository();
        $emp = $repo->findById($id);
        if ($club === null || $emp === null || $emp->clubId !== $club->id) {
            $this->flashError('Action non autorisée.');
            $this->redirect('/employes');
        }
        // Retirer aussi le compte de connexion associé, le cas échéant.
        if ($emp->userId !== null) {
            (new UserRepository())->deleteById($emp->userId);
        }
        $repo->delete($id);
        $this->flashSuccess('Employé retiré.');
        $this->redirect('/employes');
    }

    // ------------------------------------------------------------------ helpers

    /** Réserve l'accès au manager (club_owner) ou super-admin. */
    private function requireManager(): void
    {
        $u = Membership::currentUser();
        if ($u === null || (!$u->isSuperAdmin && $u->role !== 'club_owner')) {
            http_response_code(403);
            echo '<h1>403 — Accès refusé</h1><p>La gestion des employés est réservée au manager du club.</p>';
            exit;
        }
    }

    private function sendInvitation(User $user, Club $club): void
    {
        $tokens = new PasswordTokenRepository();
        $tokens->purgeForUser($user->id, 'invitation');
        $days = (int) (Bootstrap::config('app.tokens.invitation_lifetime_days') ?? 7);
        $token = $tokens->create($user->id, 'invitation', $days * 86400);

        $url = rtrim((string) Bootstrap::config('app.url'), '/') . '/set-password?token=' . urlencode($token);
        $appName = (string) Bootstrap::config('app.name');

        $body = '<p>Bonjour,</p>'
            . '<p>Un accès à l\'espace membres <strong>' . htmlspecialchars($appName) . '</strong> a été créé pour vous '
            . 'au titre du club <strong>' . htmlspecialchars($club->name) . '</strong>.</p>'
            . '<p><a href="' . htmlspecialchars($url) . '">Cliquez ici pour définir votre mot de passe</a> '
            . '(lien valable ' . $days . ' jours).</p>';

        try {
            (new Mailer())->send($user->email, $user->displayName(), 'Votre accès RESSOURCES', $body);
        } catch (\Throwable $e) {
            error_log('Invitation employé échouée : ' . $e->getMessage());
            $this->flashError('Compte créé, mais l\'email d\'invitation n\'a pas pu être envoyé (SMTP à configurer).');
        }
    }
}
