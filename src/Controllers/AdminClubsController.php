<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Bootstrap;
use App\Helpers\Csrf;
use App\Middleware\Auth;
use App\Models\Club;
use App\Models\User;
use App\Repositories\ClubRepository;
use App\Repositories\PasswordTokenRepository;
use App\Repositories\UserRepository;
use App\Services\Mailer;
use App\Session;

/**
 * Back-office super-admin — gestion des clubs abonnés.
 * Créer un club + son manager, inviter des collaborateurs (dans la limite des
 * sièges), suspendre/réactiver/fermer l'accès, éditer, supprimer.
 */
final class AdminClubsController extends BaseController
{
    private const STATUSES = ['active', 'suspended', 'closed'];

    // -------------------------------------------------------------------------
    // Liste
    // -------------------------------------------------------------------------

    public function index(): void
    {
        Auth::requireSuperAdmin();
        $clubs = (new ClubRepository())->listWithStats();
        $this->renderAdmin('pages.admin.clubs.index', [
            'title' => 'Clubs',
            'clubs' => $clubs,
        ], 'clubs', 'Clubs');
    }

    // -------------------------------------------------------------------------
    // Création
    // -------------------------------------------------------------------------

    public function showNew(): void
    {
        Auth::requireSuperAdmin();
        $this->renderAdmin('pages.admin.clubs.new', [
            'title' => 'Nouveau club',
            'old' => Session::get('club_old', []),
        ], 'clubs', 'Nouveau club');
        Session::forget('club_old');
    }

    public function create(): void
    {
        Auth::requireSuperAdmin();
        Csrf::enforce($this->input('_csrf'));

        $name = $this->input('name');
        $seats = max(1, (int) ($this->input('seats_limit') ?? '1'));
        $contactEmail = $this->input('contact_email');
        $contractRef = $this->input('contract_ref');
        $ownerEmail = $this->input('owner_email');
        $ownerName = $this->input('owner_name') ?? '';

        $errors = [];
        if ($name === null) {
            $errors[] = 'Le nom du club est requis.';
        }
        if ($ownerEmail === null || !filter_var($ownerEmail, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Un email de manager valide est requis.';
        }

        $users = new UserRepository();
        if ($ownerEmail !== null && $users->findByEmail($ownerEmail) !== null) {
            $errors[] = 'Un compte existe déjà avec cet email de manager.';
        }

        if ($errors !== []) {
            foreach ($errors as $e) {
                $this->flashError($e);
            }
            Session::set('club_old', compact('name', 'seats', 'contactEmail', 'contractRef', 'ownerEmail', 'ownerName'));
            $this->redirect('/admin/clubs/new');
        }

        $clubs = new ClubRepository();
        $club = $clubs->create((string) $name, $contactEmail, $seats, $contractRef);

        // Manager (club_owner) — compte en attente de définition de mot de passe.
        $owner = $users->create(
            email: (string) $ownerEmail,
            plainPassword: null,
            fullName: $ownerName,
            role: 'club_owner',
            clubId: $club->id,
            needsPasswordSetup: true,
        );
        $clubs->setOwner($club->id, $owner->id);

        $this->sendInvitation($owner, $club);

        $this->flashSuccess('Club créé. Une invitation a été envoyée au manager pour définir son mot de passe.');
        $this->redirect('/admin/clubs/' . $club->id);
    }

    // -------------------------------------------------------------------------
    // Détail / édition
    // -------------------------------------------------------------------------

    public function show(string $id): void
    {
        Auth::requireSuperAdmin();
        $clubs = new ClubRepository();
        $club = $clubs->findById($id);
        if ($club === null) {
            $this->notFound();
            return;
        }
        $members = (new UserRepository())->listByClub($club->id);
        $this->renderAdmin('pages.admin.clubs.detail', [
            'title' => $club->name,
            'club' => $club,
            'members' => $members,
            'seats_used' => count($members),
        ], 'clubs', $club->name);
    }

    public function update(string $id): void
    {
        Auth::requireSuperAdmin();
        Csrf::enforce($this->input('_csrf'));

        $clubs = new ClubRepository();
        $club = $clubs->findById($id);
        if ($club === null) {
            $this->notFound();
            return;
        }

        $name = $this->input('name') ?? $club->name;
        $seats = max(1, (int) ($this->input('seats_limit') ?? (string) $club->seatsLimit));
        $clubs->update($club->id, $name, $seats, $this->input('contact_email'), $this->input('contract_ref'));

        $this->flashSuccess('Club mis à jour.');
        $this->redirect('/admin/clubs/' . $club->id);
    }

    public function setStatus(string $id): void
    {
        Auth::requireSuperAdmin();
        Csrf::enforce($this->input('_csrf'));

        $clubs = new ClubRepository();
        $club = $clubs->findById($id);
        if ($club === null) {
            $this->notFound();
            return;
        }
        $status = $this->input('status');
        if (!in_array($status, self::STATUSES, true)) {
            $this->flashError('Statut invalide.');
            $this->redirect('/admin/clubs/' . $club->id);
        }
        $clubs->setStatus($club->id, (string) $status);

        $labels = ['active' => 'réactivé', 'suspended' => 'suspendu', 'closed' => 'fermé'];
        $this->flashSuccess('Accès du club ' . ($labels[$status] ?? 'mis à jour') . '.');
        $this->redirect('/admin/clubs/' . $club->id);
    }

    public function delete(string $id): void
    {
        Auth::requireSuperAdmin();
        Csrf::enforce($this->input('_csrf'));

        $clubs = new ClubRepository();
        $club = $clubs->findById($id);
        if ($club !== null) {
            $clubs->delete($club->id);
            $this->flashSuccess('Club et comptes associés supprimés.');
        }
        $this->redirect('/admin/clubs');
    }

    // -------------------------------------------------------------------------
    // Membres (collaborateurs)
    // -------------------------------------------------------------------------

    public function inviteMember(string $id): void
    {
        Auth::requireSuperAdmin();
        Csrf::enforce($this->input('_csrf'));

        $clubs = new ClubRepository();
        $users = new UserRepository();
        $club = $clubs->findById($id);
        if ($club === null) {
            $this->notFound();
            return;
        }

        $email = $this->input('member_email');
        $fullName = $this->input('member_name') ?? '';

        if ($email === null || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->flashError('Email du collaborateur invalide.');
            $this->redirect('/admin/clubs/' . $club->id);
        }
        if ($users->findByEmail($email) !== null) {
            $this->flashError('Un compte existe déjà avec cet email.');
            $this->redirect('/admin/clubs/' . $club->id);
        }
        if ($users->countByClub($club->id) >= $club->seatsLimit) {
            $this->flashError('Nombre de sièges atteint (' . $club->seatsLimit . '). Augmentez la limite pour ajouter un collaborateur.');
            $this->redirect('/admin/clubs/' . $club->id);
        }

        $member = $users->create(
            email: (string) $email,
            plainPassword: null,
            fullName: $fullName,
            role: 'club_member',
            clubId: $club->id,
            needsPasswordSetup: true,
        );
        $this->sendInvitation($member, $club);

        $this->flashSuccess('Collaborateur invité par email.');
        $this->redirect('/admin/clubs/' . $club->id);
    }

    public function removeMember(string $id, string $userId): void
    {
        Auth::requireSuperAdmin();
        Csrf::enforce($this->input('_csrf'));

        $clubs = new ClubRepository();
        $club = $clubs->findById($id);
        if ($club === null) {
            $this->notFound();
            return;
        }
        if ($userId === $club->ownerUserId) {
            $this->flashError('Impossible de retirer le manager du club. Désignez d\'abord un autre manager.');
            $this->redirect('/admin/clubs/' . $club->id);
        }
        (new UserRepository())->deleteById($userId);
        $this->flashSuccess('Collaborateur retiré du club.');
        $this->redirect('/admin/clubs/' . $club->id);
    }

    public function resendInvitation(string $id, string $userId): void
    {
        Auth::requireSuperAdmin();
        Csrf::enforce($this->input('_csrf'));

        $clubs = new ClubRepository();
        $club = $clubs->findById($id);
        $user = (new UserRepository())->findById($userId);
        if ($club === null || $user === null || $user->clubId !== $club->id) {
            $this->notFound();
            return;
        }
        $this->sendInvitation($user, $club);
        $this->flashSuccess('Invitation renvoyée à ' . $user->email . '.');
        $this->redirect('/admin/clubs/' . $club->id);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

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
            . '(lien valable ' . $days . ' jours).</p>'
            . '<p>À bientôt,<br>L\'équipe RESSOURCES</p>';

        try {
            (new Mailer())->send($user->email, $user->displayName(), 'Votre accès RESSOURCES', $body);
        } catch (\Throwable $e) {
            error_log('Invitation email échouée : ' . $e->getMessage());
            $this->flashError('Compte créé, mais l\'email d\'invitation n\'a pas pu être envoyé (SMTP). Vérifiez la config mail.');
        }
    }

    private function notFound(): void
    {
        http_response_code(404);
        $this->render('pages.errors.404', ['title' => 'Club introuvable']);
    }

    /** @param array<string,mixed> $data */
    private function renderAdmin(string $view, array $data, string $active, string $pageTitle): void
    {
        $this->render($view, layout: 'layouts.admin', data: array_merge($data, [
            'admin' => [
                'active' => $active,
                'page_title' => $pageTitle,
                'user_name' => (string) Session::get('user_full_name', ''),
            ],
        ]));
    }
}
