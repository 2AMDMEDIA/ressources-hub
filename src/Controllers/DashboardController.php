<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Middleware\Membership;

final class DashboardController extends BaseController
{
    public function index(): void
    {
        // Paywall : club actif ET membre actif (super-admin passe outre).
        $club = Membership::guard();
        $user = Membership::currentUser();

        $this->renderApp('pages.dashboard.index', [
            'title' => 'Tableau de bord',
            'user' => $user,
            'club' => $club,
        ], ['active' => 'dashboard', 'page_title' => 'Tableau de bord']);
    }
}
