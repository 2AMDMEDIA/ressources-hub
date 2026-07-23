<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Csrf;
use App\Middleware\Auth;
use App\Repositories\ClubRepository;
use App\Repositories\EmployeeRepository;
use App\Session;

/**
 * Back-office super-admin — employés des clubs (équipe).
 * Un club peut avoir plusieurs employés (fiches, sans compte de connexion).
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

    /** Ajoute un employé à un club (depuis la fiche club). */
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

        (new EmployeeRepository())->create($clubId, (string) $first, (string) $last, $email, $this->input('job_title'));
        $this->flashSuccess('Employé ajouté.');
        $this->redirect('/admin/clubs/' . $clubId);
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
        $repo->delete($id);
        $this->flashSuccess('Employé retiré.');
        $this->redirect('/admin/clubs/' . $clubId);
    }
}
