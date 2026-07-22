<?php

use App\Helpers\Renderer;

/**
 * Page servie par le paywall quand l'accès est refusé (membre ou club suspendu).
 *
 * @var string $reason_title
 * @var string $reason_message
 * @var string $csrf_token
 */
?><!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accès suspendu — RESSOURCES</title>
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="auth-body">
    <main class="auth-shell">
        <div class="auth-card">
            <header class="auth-header">
                <h1 class="auth-brand">RESSOURCES</h1>
            </header>

            <h2 class="auth-title"><?= Renderer::escape($reason_title) ?></h2>
            <p class="auth-helper"><?= Renderer::escape($reason_message) ?></p>

            <a href="mailto:ressources@fitness-challenges.com" class="btn btn--primary btn--block">
                Contacter l'équipe RESSOURCES
            </a>

            <form method="POST" action="/logout" style="margin-top:16px;">
                <input type="hidden" name="_csrf" value="<?= Renderer::escape($csrf_token) ?>">
                <button type="submit" class="btn btn--ghost btn--block">Se déconnecter</button>
            </form>
        </div>
    </main>
</body>
</html>
