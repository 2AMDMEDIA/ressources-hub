<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Session;

final class HomeController extends BaseController
{
    public function index(): void
    {
        if (Session::isLoggedIn()) {
            // Lot 1 : pas encore de back-office /admin (lot 3) → tout le monde sur /dashboard.
            $this->redirect('/dashboard');
        }
        $this->redirect('/login');
    }
}
