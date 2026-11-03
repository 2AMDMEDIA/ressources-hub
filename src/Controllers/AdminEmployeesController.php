<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Bootstrap;
use App\Helpers\Csrf;
use App\Middleware\Auth;
use App\Models\Club;
use App\Models\Employee;
use App\Models\User;
use App\Repositories\ClubRepository;
use App\Repositories\EmployeeRepository;
use App\Repositories\PasswordTokenRepository;
use App\Repositories\UserRepository;
use App\Services\Mailer;
use App\Session;

/**
 * Back-office super-admin — employés des clubs (équipe).
 * Un employé = une fiche. Avec un email, on lui crée un accès de connexion et on
 * lui envoie une invitation (lien de création de mot de passe).
 */
final class AdminEmployeesController extends BaseController
{
    /** Liste globale de tous les employés avec leur club. */
    public function index(): void
    {
        Auth::requireSuperAdmin();
        $employees = (new EmployeeRepository())->listAllWithClub();
        $this->render('pages.admin.employees.index', layout: 'layouts.admin', data: [
            'title' => 'Employés',
            'employees' => $employees,
            'admin' => ['active' => 'employees', 'page_title' => 'Employés', 'user_name' => (string) Session::get('user_full_name', '')],
        ]);
    }

    /** Ajoute un employé à un club. Avec un email → accès créé + invitation envoyée. */
    public function store(string $clubId): void
    {
        Auth::requireSuperAdmin();
        Csrf::enforce($this->input('_csrf'));

        $club = (new ClubRepository())->findById($clubId);
        if ($club === null) {
            http_response_code(404);
            $this->render('pages.errors.404', ['title' => 'Club introuvable']);
            return;
        }

        $first = $this->input('first_name');
        $last = $this->input('last_name');
        if ($first === null || $last === null) {
            $this->flashError('Prénom et nom de l\'employé requis.');
            $this->redirect('/admin/clubs/' . $clubId);
        }
        $email = $this->input('email');
        if ($email !== null && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->flashError('Email de l\'employé invalide.');
            $this->redirect('/admin/clubs/' . $clubId);
        }

        $repo = new EmployeeRepository();
        $empId = $repo->create($clubId, (string) $first, (string) $last, $email, $this->input('job_title'));
        $this->flashSuccess('Employé ajouté.');

        // Avec un email : on crée l'accès et on envoie l'invitation (création de mot de passe).
        if ($email !== null) {
            $emp = $repo->findById($empId);
            if ($emp !== null) {
                $err = $this->createAccessAndInvite($emp, $club);
                if ($err === null) {
                    $this->flashSuccess('Invitation envoyée à ' . $email . ' (création de mot de passe).');
                } else {
                    $this->flashError($err);
                }
            }
        }

        $this->redirect('/admin/clubs/' . $clubId);
    }

    /** Crée l'accès de connexion d'un employé + envoie l'invitation (bouton « Donner l'accès »). */
    public function grantAccess(string $id): void
    {
        Auth::requireSuperAdmin();
        Csrf::enforce($this->input('_csrf'));

        $emp = (new EmployeeRepository())->findById($id);
        if ($emp === null) {
            $this->redirect('/admin/employees');
        }
        $club = (new ClubRepository())->findById($emp->clubId);
        if ($club === null) {
            $this->flashError('Club introuvable.');
            $this->redirect('/admin/employees');
        }

        $err = $this->createAccessAndInvite($emp, $club);
        if ($err === null) {
            $this->flashSuccess('Accès créé — invitation envoyée à ' . $emp->email . '.');
        } else {
            $this->flashError($err);
        }
        $this->redirect('/admin/clubs/' . $emp->clubId);
    }

    public function update(string $id): void
    {
        Auth::requireSuperAdmin();
        Csrf::enforce($this->input('_csrf'));

        $repo = new EmployeeRepository();
        $emp = $repo->findById($id);
        if ($emp === null) {
            $this->redirect('/admin/employees');
        }

        $first = $this->input('first_name');
        $last = $this->input('last_name');
        if ($first === null || $last === null) {
            $this->flashError('Prénom et nom requis.');
            $this->redirect('/admin/clubs/' . $emp->clubId);
        }
        $email = $this->input('email');
        if ($email !== null && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->flashError('Email invalide.');
            $this->redirect('/admin/clubs/' . $emp->clubId);
        }

        $repo->update($id, (string) $first, (string) $last, $email, $this->input('job_title'));
        $this->flashSuccess('Employé mis à jour.');
        $this->redirect('/admin/clubs/' . $emp->clubId);
    }

    public function delete(string $id): void
    {
        Auth::requireSuperAdmin();
        Csrf::enforce($this->input('_csrf'));

        $repo = new EmployeeRepository();
        $emp = $repo->findById($id);
        if ($emp === null) {
            $this->redirect('/admin/employees');
        }
        $clubId = $emp->clubId;
        // Retirer aussi le compte de connexion associé, le cas échéant.
        if ($emp->userId !== null) {
            (new UserRepository())->deleteById($emp->userId);
        }
        $repo->delete($id);
        $this->flashSuccess('Employé retiré.');
        $this->redirect('/admin/clubs/' . $clubId);
    }

    // ------------------------------------------------------------------ helpers

    /**
     * Crée le compte de connexion d'un employé et lui envoie l'invitation.
     * @return ?string message d'erreur, ou null si tout s'est bien passé
     */
    private function createAccessAndInvite(Employee $emp, Club $club): ?string
    {
        if ($emp->hasAccess()) {
            return 'Cet employé a déjà un accès.';
        }
        if ($emp->email === null || !filter_var($emp->email, FILTER_VALIDATE_EMAIL)) {
            return 'Un email valide est requis pour créer un accès.';
        }
        $users = new UserRepository();
        if ($users->findByEmail($emp->email) !== null) {
            return 'Un compte existe déjà avec cet email.';
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
        (new EmployeeRepository())->setUserId($emp->id, $user->id);

        return $this->sendInvitation($user, $club)
            ? null
            : 'Accès créé, mais l\'email d\'invitation n\'a pas pu être envoyé (voir storage/logs).';
    }

    /** Envoie l'email d'invitation (lien de création de mot de passe). */
    private function sendInvitation(User $user, Club $club): bool
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
            return true;
        } catch (\Throwable $e) {
            error_log('Invitation employé (admin) échouée : ' . $e->getMessage());
            return false;
        }
    }
}
