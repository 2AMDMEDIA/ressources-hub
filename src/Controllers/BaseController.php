<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Csrf;
use App\Helpers\Renderer;
use App\Middleware\Membership;
use App\Session;

/**
 * Base de tous les contrôleurs : utilitaires render, redirect, JSON, input.
 */
abstract class BaseController
{
    /**
     * Rend un template et envoie la réponse.
     *
     * @param array<string,mixed> $data
     */
    protected function render(string $view, array $data = [], ?string $layout = null): void
    {
        $defaults = [
            'flashes' => Session::takeFlashes(),
            'csrf_token' => Csrf::token(),
            'is_logged_in' => Session::isLoggedIn(),
            'current_user_id' => Session::userId(),
        ];
        $merged = array_merge($defaults, $data);
        if ($layout !== null) {
            Renderer::outputWithLayout($layout, $view, $merged);
            return;
        }
        Renderer::output($view, $merged);
    }

    /**
     * Rend une page de l'espace membre dans le layout applicatif (header + navigation).
     * Le contexte membre/club provient du dernier Membership::guard().
     *
     * @param array<string,mixed> $data
     * @param array{active?:string,page_title?:string} $options
     */
    protected function renderApp(string $view, array $data = [], array $options = []): void
    {
        $user = Membership::currentUser();
        $club = Membership::currentClub();

        $chrome = [
            'active' => $options['active'] ?? '',
            'page_title' => $options['page_title'] ?? ($data['title'] ?? 'RESSOURCES'),
            'user_name' => $user?->displayName() ?? (string) Session::get('user_full_name', ''),
            'user_email' => $user?->email ?? (string) Session::get('user_email', ''),
            'is_super_admin' => (bool) Session::get('is_super_admin', false),
            'role' => $user?->role ?? (string) Session::get('role', ''),
            'club_name' => $club?->name,
        ];

        $this->render($view, layout: 'layouts.app', data: array_merge($data, [
            'chrome' => $chrome,
            'title' => $chrome['page_title'],
        ]));
    }

    protected function redirect(string $location): void
    {
        header('Location: ' . $location);
        exit;
    }

    protected function back(): void
    {
        $this->redirect($_SERVER['HTTP_REFERER'] ?? '/');
    }

    /** @param array<string,mixed> $payload */
    protected function json(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    protected function input(string $key, ?string $default = null): ?string
    {
        $value = $_POST[$key] ?? $_GET[$key] ?? $default;
        if (is_string($value)) {
            $value = trim($value);
        }
        return $value === '' ? $default : (is_string($value) ? $value : $default);
    }

    protected function inputBool(string $key): bool
    {
        return filter_var($_POST[$key] ?? $_GET[$key] ?? false, FILTER_VALIDATE_BOOLEAN);
    }

    protected function flashError(string $message): void
    {
        Session::flash('error', $message);
    }

    protected function flashSuccess(string $message): void
    {
        Session::flash('success', $message);
    }
}
