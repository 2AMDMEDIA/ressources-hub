<?php

use App\Helpers\Renderer;

/**
 * Layout de l'espace membre : colonne de gauche (navigation) + contenu.
 * La bibliothèque (10 catégories du CDC) est affichée mais "Bientôt" tant que
 * les pages ne sont pas construites.
 *
 * @var string $title
 * @var array{active:string,page_title:string,user_name:string,user_email:string,is_super_admin:bool,club_name:?string} $chrome
 * @var array<int,array{type:string,message:string}> $flashes
 * @var string $csrf_token
 * @var array{name:string,version:string,url:string} $app
 * @var string $content_html
 */
$nav = [
    ['dashboard', '/dashboard', 'Tableau de bord'],
];
$library = [
    'Accueil', 'Vente', 'Marketing', 'Fidélisation', 'Offre & Services',
    'Ressources Humaines', 'Pilotage & KPI', 'Anticiper Demain',
    'Création & Lancement', 'Masterclasses & Lives',
];
?><!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Renderer::escape($title) ?> — <?= Renderer::escape($app['name']) ?></title>
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="app-body">
<div class="app-shell">
    <input type="checkbox" id="msidebar" class="msidebar-toggle" hidden>
    <aside class="sidebar member-sidebar">
        <div class="sidebar__brand">
            <span class="sidebar__brand-name">RESSOURCES</span>
            <span class="sidebar__brand-version">Espace membre</span>
        </div>
        <?php if (!empty($chrome['club_name'])): ?>
            <div class="sidebar__club"><?= Renderer::escape($chrome['club_name']) ?></div>
        <?php endif; ?>
        <nav class="sidebar__nav">
            <?php foreach ($nav as [$key, $href, $label]): ?>
                <a href="<?= $href ?>" class="sidebar__item<?= $chrome['active'] === $key ? ' sidebar__item--active' : '' ?>">
                    <span class="sidebar__label"><?= Renderer::escape($label) ?></span>
                </a>
            <?php endforeach; ?>

            <div class="sidebar__separator">Bibliothèque</div>
            <?php foreach ($library as $label): ?>
                <span class="sidebar__item sidebar__item--disabled">
                    <span class="sidebar__label"><?= Renderer::escape($label) ?></span>
                    <span class="sidebar__badge">Bientôt</span>
                </span>
            <?php endforeach; ?>

            <div class="sidebar__separator">Mon espace</div>
            <a href="/compte" class="sidebar__item<?= $chrome['active'] === 'account' ? ' sidebar__item--active' : '' ?>">
                <span class="sidebar__label">Mon compte</span>
            </a>
        </nav>
    </aside>

    <div class="app-main">
        <header class="app-topbar">
            <div class="app-topbar__left">
                <label for="msidebar" class="app-topbar__burger" aria-label="Menu"><span></span><span></span><span></span></label>
                <h1 class="app-topbar__title"><?= Renderer::escape($chrome['page_title']) ?></h1>
            </div>
            <div class="app-topbar__right">
                <a href="/compte" class="app-topbar__user"><?= Renderer::escape($chrome['user_name']) ?></a>
                <form method="POST" action="/logout" style="margin:0;">
                    <input type="hidden" name="_csrf" value="<?= Renderer::escape($csrf_token) ?>">
                    <button type="submit" class="btn btn--ghost btn--sm">Déconnexion</button>
                </form>
            </div>
        </header>

        <div class="app-content">
            <?php if (!empty($flashes)): ?>
                <div class="flashes" style="margin-bottom:16px;">
                    <?php foreach ($flashes as $flash): ?>
                        <div class="flash flash--<?= Renderer::escape($flash['type']) ?>"><?= Renderer::escape($flash['message']) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?= $content_html ?>
        </div>
    </div>
</div>
</body>
</html>
