<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Csrf;
use App\Middleware\Auth;
use App\Services\MigrationRunner;
use App\Session;

/**
 * Back-office super-admin — application des migrations de base de données.
 * S'appuie sur MigrationRunner (table schema_migrations) : n'applique que les
 * migrations en attente, ne rejoue jamais une migration déjà passée.
 */
final class AdminMigrationsController extends BaseController
{
    public function index(): void
    {
        Auth::requireSuperAdmin();
        $runner = new MigrationRunner();
        $migrations = [];
        $error = null;
        try {
            $migrations = $runner->listAll();
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }
        $this->render('pages.admin.migrations.index', layout: 'layouts.admin', data: [
            'title' => 'Migrations',
            'migrations' => $migrations,
            'db_error' => $error,
            'admin' => ['active' => 'migrations', 'page_title' => 'Migrations base de données', 'user_name' => (string) Session::get('user_full_name', '')],
        ]);
    }

    public function run(): void
    {
        Auth::requireSuperAdmin();
        Csrf::enforce($this->input('_csrf'));

        $report = (new MigrationRunner())->applyPending();
        $applied = count($report['applied']);

        if ($report['failed'] !== null) {
            $this->flashError(sprintf(
                '%d migration(s) appliquée(s), puis échec sur %s : %s',
                $applied,
                $report['failed']['name'],
                $report['failed']['error'],
            ));
        } elseif ($applied === 0) {
            $this->flashSuccess('Base déjà à jour, aucune migration en attente.');
        } else {
            $this->flashSuccess($applied . ' migration(s) appliquée(s) avec succès.');
        }
        $this->redirect('/admin/migrations');
    }
}
