<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Helpers\Renderer;
use App\Models\Club;
use App\Models\User;
use App\Repositories\ClubRepository;
use App\Repositories\UserRepository;
use App\Session;

/**
 * Paywall RESSOURCES — cœur de l'espace membre.
 *
 * L'accès à la bibliothèque est conditionné (CDC §2.2) à :
 *   - un membre au statut "active"  ET
 *   - un club au statut "active".
 *
 * La vérification est refaite EN BASE à chaque requête protégée (pas seulement
 * depuis la session) : si l'admin suspend un club ou un membre, l'accès est coupé
 * immédiatement à la requête suivante, sans attendre l'expiration de la session.
 *
 * Les super-admins (équipe MD MEDIA) contournent le paywall — ils n'ont pas de club.
 */
final class Membership
{
    private static ?User $user = null;
    private static ?Club $club = null;

    /**
     * Garde une page de l'espace membre. Retourne le club actif (null pour un super-admin).
     * En cas d'accès refusé, rend une page dédiée et coupe l'exécution.
     */
    public static function guard(): ?Club
    {
        Auth::require();

        $userId = Session::userId();
        $user = (new UserRepository())->findById((string) $userId);

        // Session orpheline (user supprimé) → on repart proprement sur le login.
        if ($user === null) {
            Session::destroy();
            header('Location: /login');
            exit;
        }

        self::$user = $user;

        // Super-admin : accès inconditionnel, pas de club.
        if ($user->isSuperAdmin) {
            return null;
        }

        // Membre suspendu (défaut de paiement, désactivation manuelle…).
        if (!$user->isActive()) {
            self::deny(
                'Votre compte a été suspendu.',
                'Contactez l\'équipe RESSOURCES pour réactiver votre accès.'
            );
        }

        // Membre sans club rattaché : anomalie de données → on bloque proprement.
        if ($user->clubId === null) {
            self::deny(
                'Aucun club n\'est rattaché à votre compte.',
                'Contactez l\'équipe RESSOURCES.'
            );
        }

        $club = (new ClubRepository())->findById($user->clubId);
        if ($club === null || !$club->grantsAccess()) {
            self::deny(
                'L\'accès de votre club est actuellement fermé.',
                'Cet accès est lié au statut de votre abonnement RESSOURCES. Contactez l\'équipe pour le rétablir.'
            );
        }

        self::$club = $club;
        return $club;
    }

    /** Membre courant résolu par le dernier guard() (null avant appel). */
    public static function currentUser(): ?User
    {
        return self::$user;
    }

    /** Club courant résolu par le dernier guard() (null pour super-admin ou avant appel). */
    public static function currentClub(): ?Club
    {
        return self::$club;
    }

    private static function deny(string $title, string $message): never
    {
        http_response_code(403);
        echo Renderer::render('pages.errors.suspended', [
            'title' => 'Accès suspendu',
            'reason_title' => $title,
            'reason_message' => $message,
            'csrf_token' => \App\Helpers\Csrf::token(),
        ]);
        exit;
    }
}
