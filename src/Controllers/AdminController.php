<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Middleware\Auth;

final class AdminController extends BaseController
{
    /** Landing back-office : redirige vers la gestion des clubs. */
    public function index(): void
    {
        Auth::requireSuperAdmin();
        $this->redirect('/admin/clubs');
    }
}
