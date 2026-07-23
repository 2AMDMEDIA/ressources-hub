<?php

declare(strict_types=1);

use App\Controllers\AdminClubsController;
use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\InstallController;
use App\Controllers\SiteController;

/**
 * Table de routage RESSOURCES.
 *
 * Format : [méthode HTTP, chemin (regex friendly avec {param}), [Controller, action], 'auth']
 *   - auth = false        → route publique
 *   - auth = true         → session requise (le paywall "club actif ET membre actif"
 *                           est appliqué dans le contrôleur via Membership::guard())
 *   - auth = 'super-admin' → session requise + flag super-admin
 *
 * Lot 1 : fondations (accueil, install, auth, dashboard sous paywall).
 * Les lots suivants ajouteront : back-office contenus, bibliothèque, lecteur Vimeo, RAG/chatbot.
 */
return [
    // -- Site vitrine public (accessible à tous) --
    ['GET',  '/', [SiteController::class, 'home'], false],
    ['GET',  '/experts', [SiteController::class, 'experts'], false],
    ['GET',  '/prix', [SiteController::class, 'pricing'], false],
    ['GET',  '/contact', [SiteController::class, 'contact'], false],
    ['POST', '/contact', [SiteController::class, 'submitContact'], false],

    // Installation one-shot (token-protégée via INSTALL_TOKEN dans .env)
    ['GET',  '/install', [InstallController::class, 'show'], false],
    ['POST', '/install', [InstallController::class, 'run'], false],

    // Auth (toutes publiques)
    ['GET',  '/login', [AuthController::class, 'showLogin'], false],
    ['POST', '/login', [AuthController::class, 'login'], false],
    ['POST', '/logout', [AuthController::class, 'logout'], true],
    ['GET',  '/forgot-password', [AuthController::class, 'showForgotPassword'], false],
    ['POST', '/forgot-password', [AuthController::class, 'sendResetLink'], false],
    ['GET',  '/reset-password', [AuthController::class, 'showResetPassword'], false],
    ['POST', '/reset-password', [AuthController::class, 'resetPassword'], false],
    ['GET',  '/set-password', [AuthController::class, 'showSetPassword'], false],
    ['POST', '/set-password', [AuthController::class, 'setPassword'], false],

    // Espace membre — protégé par le paywall (club actif ET membre actif)
    ['GET', '/dashboard', [DashboardController::class, 'index'], true],

    // -- Back-office super-admin : gestion des clubs --
    ['GET',  '/admin', [AdminController::class, 'index'], 'super-admin'],
    ['GET',  '/admin/clubs', [AdminClubsController::class, 'index'], 'super-admin'],
    ['GET',  '/admin/clubs/new', [AdminClubsController::class, 'showNew'], 'super-admin'],
    ['POST', '/admin/clubs', [AdminClubsController::class, 'create'], 'super-admin'],
    ['GET',  '/admin/clubs/{id}', [AdminClubsController::class, 'show'], 'super-admin'],
    ['POST', '/admin/clubs/{id}', [AdminClubsController::class, 'update'], 'super-admin'],
    ['POST', '/admin/clubs/{id}/status', [AdminClubsController::class, 'setStatus'], 'super-admin'],
    ['POST', '/admin/clubs/{id}/delete', [AdminClubsController::class, 'delete'], 'super-admin'],
    ['POST', '/admin/clubs/{id}/members', [AdminClubsController::class, 'inviteMember'], 'super-admin'],
    ['POST', '/admin/clubs/{id}/members/{userId}/remove', [AdminClubsController::class, 'removeMember'], 'super-admin'],
    ['POST', '/admin/clubs/{id}/members/{userId}/resend', [AdminClubsController::class, 'resendInvitation'], 'super-admin'],
];
