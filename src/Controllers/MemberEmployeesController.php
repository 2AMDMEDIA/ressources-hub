<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Csrf;
use App\Middleware\Membership;
use App\Repositories\EmployeeRepository;

/**
 * Espace membre — gestion des employés de SON club par le manager.
 * Isolation stricte : on ne manipule que les employés du club courant.
 */
final class MemberEmployeesController extends BaseController
{
    public function index(): void
    {
        $club = Membership::guard();

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

    public function delete(string $id): void
    {
        $club = Membership::guard();
        Csrf::enforce($this->input('_csrf'));

        $repo = new EmployeeRepository();
        $emp = $repo->findById($id);
        // Sécurité : l'employé doit appartenir au club du membre connecté.
        if ($club === null || $emp === null || $emp->clubId !== $club->id) {
            $this->flashError('Action non autorisée.');
            $this->redirect('/employes');
        }
        $repo->delete($id);
        $this->flashSuccess('Employé retiré.');
        $this->redirect('/employes');
    }
}
