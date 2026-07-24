<?php

use App\Helpers\Renderer;

/**
 * Layout back-office super-admin (sidebar + topbar).
 *
 * @var string $title
 * @var array{active:string,page_title:string,user_name:string} $admin
 * @var array<int,array{type:string,message:string}> $flashes
 * @var string $csrf_token
 * @var array{name:string,version:string,url:string} $app
 * @var string $content_html
 */
$nav = [
    ['clubs', '/admin/clubs', 'Clubs'],
    ['employees', '/admin/employees', 'Employés'],
    ['resources', '/admin/resources', 'Ressources'],
    ['categories', '/admin/categories', 'Catégories'],
];
?><!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Renderer::escape($admin['page_title']) ?> — Admin RESSOURCES</title>
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="app-body">
<div class="app-shell">
    <aside class="sidebar admin-sidebar">
        <div class="sidebar__brand">
            <span class="sidebar__brand-name">RESSOURCES</span>
            <span class="sidebar__brand-version">Admin</span>
        </div>
        <nav class="sidebar__nav">
            <?php foreach ($nav as [$key, $href, $label]): ?>
                <a href="<?= $href ?>" class="sidebar__item<?= $admin['active'] === $key ? ' sidebar__item--active' : '' ?>">
                    <span class="sidebar__label"><?= Renderer::escape($label) ?></span>
                </a>
            <?php endforeach; ?>
            <div class="sidebar__separator">Configuration</div>
            <a href="/admin/settings" class="sidebar__item<?= $admin['active'] === 'settings' ? ' sidebar__item--active' : '' ?>">
                <span class="sidebar__label">Paramètres</span>
            </a>

            <div class="sidebar__separator">Bientôt</div>
            <span class="sidebar__item sidebar__item--disabled"><span class="sidebar__label">Messages</span></span>
        </nav>
        <a href="/" class="sidebar__item sidebar__foot">← Voir le site public</a>
    </aside>

    <div class="app-main">
        <header class="admin-topbar">
            <h1 class="admin-topbar__title"><?= Renderer::escape($admin['page_title']) ?></h1>
            <div class="admin-topbar__right">
                <span class="admin-topbar__user"><?= Renderer::escape($admin['user_name']) ?></span>
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
