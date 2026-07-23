<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Bootstrap;
use App\Helpers\Csrf;
use App\Middleware\Auth;
use App\Models\Club;
use App\Models\User;
use App\Repositories\ClubManagerRepository;
use App\Repositories\ClubRepository;
use App\Repositories\PasswordTokenRepository;
use App\Repositories\UserRepository;
use App\Services\Mailer;
use App\Session;

/**
 * Back-office super-admin — gestion des clubs et de leur manager unique.
 * Un club a exactement un manager, lié via la table `club_managers`.
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
        $this->renderAdmin('pages.admin.clubs.index', ['title' => 'Clubs', 'clubs' => $clubs], 'clubs', 'Clubs');
    }

    // -------------------------------------------------------------------------
    // Création (club + manager)
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
        $mFirst = $this->input('manager_first_name');
        $mLast = $this->input('manager_last_name');
        $mEmail = $this->input('manager_email');
        $mJob = $this->input('manager_job_title');

        $errors = [];
        if ($name === null) {
            $errors[] = 'Le nom du club est requis.';
        }
        if ($mFirst === null || $mLast === null) {
            $errors[] = 'Le prénom et le nom du manager sont requis.';
        }
        if ($mEmail === null || !filter_var($mEmail, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Un email de manager valide est requis.';
        }
        $users = new UserRepository();
        if ($mEmail !== null && $users->findByEmail($mEmail) !== null) {
            $errors[] = 'Un compte existe déjà avec cet email de manager.';
        }

        if ($errors !== []) {
            foreach ($errors as $e) {
                $this->flashError($e);
            }
            Session::set('club_old', $_POST);
            $this->redirect('/admin/clubs/new');
        }

        $clubs = new ClubRepository();
        $club = $clubs->create((string) $name, $this->clubData());

        $manager = $users->create(
            email: (string) $mEmail,
            plainPassword: null,
            role: 'club_owner',
            clubId: $club->id,
            needsPasswordSetup: true,
            firstName: $mFirst,
            lastName: $mLast,
            jobTitle: $mJob,
        );
        (new ClubManagerRepository())->setManager($club->id, $manager->id);
        $this->sendInvitation($manager, $club);

        $this->flashSuccess('Club créé. Une invitation a été envoyée au manager pour définir son mot de passe.');
        $this->redirect('/admin/clubs/' . $club->id);
    }

    // -------------------------------------------------------------------------
    // Détail / édition
    // -------------------------------------------------------------------------

    public function show(string $id): void
    {
        Auth::requireSuperAdmin();
        $club = (new ClubRepository())->findById($id);
        if ($club === null) {
            $this->notFound();
            return;
        }
        $manager = (new ClubManagerRepository())->managerOf($club->id);
        $employees = (new \App\Repositories\EmployeeRepository())->listByClub($club->id);
        $this->renderAdmin('pages.admin.clubs.detail', [
            'title' => $club->name,
            'club' => $club,
            'manager' => $manager,
            'employees' => $employees,
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
        $clubs->update($club->id, $name, $this->clubData());

        $this->flashSuccess('Club mis à jour.');
        $this->redirect('/admin/clubs/' . $club->id);
    }

    /** Le super-admin modifie les informations du manager. */
    public function updateManager(string $id): void
    {
        Auth::requireSuperAdmin();
        Csrf::enforce($this->input('_csrf'));

        $clubs = new ClubRepository();
        $club = $clubs->findById($id);
        if ($club === null) {
            $this->notFound();
            return;
        }
        $manager = (new ClubManagerRepository())->managerOf($club->id);
        if ($manager === null) {
            $this->flashError('Ce club n\'a pas encore de manager.');
            $this->redirect('/admin/clubs/' . $club->id);
        }

        $first = $this->input('manager_first_name');
        $last = $this->input('manager_last_name');
        $email = $this->input('manager_email');
        $job = $this->input('manager_job_title');

        if ($first === null || $last === null || $email === null || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->flashError('Prénom, nom et email valide du manager requis.');
            $this->redirect('/admin/clubs/' . $club->id);
        }

        $users = new UserRepository();
        $other = $users->findByEmail($email);
        if ($other !== null && $other->id !== $manager->id) {
            $this->flashError('Cet email est déjà utilisé par un autre compte.');
            $this->redirect('/admin/clubs/' . $club->id);
        }

        $users->updateProfile($manager->id, (string) $first, (string) $last, (string) $email, $job);
        $this->flashSuccess('Informations du manager mises à jour.');
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
            $this->flashSuccess('Club et compte manager supprimés.');
        }
        $this->redirect('/admin/clubs');
    }

    public function resendInvitation(string $id): void
    {
        Auth::requireSuperAdmin();
        Csrf::enforce($this->input('_csrf'));

        $club = (new ClubRepository())->findById($id);
        $manager = $club !== null ? (new ClubManagerRepository())->managerOf($club->id) : null;
        if ($club === null || $manager === null) {
            $this->notFound();
            return;
        }
        $this->sendInvitation($manager, $club);
        $this->flashSuccess('Invitation renvoyée à ' . $manager->email . '.');
        $this->redirect('/admin/clubs/' . $club->id);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /** @return array<string,mixed> Champs établissement depuis le POST. */
    private function clubData(): array
    {
        $area = $this->input('area_sqm');
        $year = $this->input('opening_year');
        return [
            'siret' => $this->input('siret'),
            'address' => $this->input('address'),
            'postal_code' => $this->input('postal_code'),
            'city' => $this->input('city'),
            'country' => $this->input('country') ?? 'France',
            'area_sqm' => ($area !== null && $area !== '') ? (int) $area : null,
            'opening_year' => ($year !== null && $year !== '') ? (int) $year : null,
        ];
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
