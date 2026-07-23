<?php

use App\Helpers\Renderer;

/**
 * Layout de l'espace membre (lot 1 : header + contenu).
 * La navigation latérale complète (10 catégories) arrivera au lot 2.
 *
 * @var string $title
 * @var array{active:string,page_title:string,user_name:string,user_email:string,is_super_admin:bool,club_name:?string} $chrome
 * @var array<int,array{type:string,message:string}> $flashes
 * @var string $csrf_token
 * @var array{name:string,version:string,url:string} $app
 * @var string $content_html
 */
?><!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Renderer::escape($title) ?> — <?= Renderer::escape($app['name']) ?></title>
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="app-body">
    <header class="appbar">
        <div class="appbar__brand">
            <span class="appbar__logo">RESSOURCES</span>
            <span class="appbar__tagline">by Fitness Challenges</span>
        </div>
        <div class="appbar__right">
            <?php if (!empty($chrome['club_name'])): ?>
                <span class="appbar__club"><?= Renderer::escape($chrome['club_name']) ?></span>
            <?php elseif ($chrome['is_super_admin']): ?>
                <span class="appbar__club appbar__club--admin">Administration</span>
            <?php endif; ?>
            <a href="/compte" class="appbar__user appbar__user--link"><?= Renderer::escape($chrome['user_name']) ?></a>
            <form method="POST" action="/logout" class="appbar__logout">
                <input type="hidden" name="_csrf" value="<?= Renderer::escape($csrf_token) ?>">
                <button type="submit" class="btn btn--ghost btn--sm">Déconnexion</button>
            </form>
        </div>
    </header>

    <main class="member-main">
        <?php if (!empty($flashes)): ?>
            <div class="flashes">
                <?php foreach ($flashes as $flash): ?>
                    <div class="flash flash--<?= Renderer::escape($flash['type']) ?>">
                        <?= Renderer::escape($flash['message']) ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?= $content_html ?>
    </main>
</body>
</html>
