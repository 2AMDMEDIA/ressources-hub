<?php

use App\Helpers\Renderer;
use App\Models\Club;
use App\Models\User;

/**
 * @var User $user
 * @var ?Club $club
 */
?>
<div class="page-head">
    <h1 class="page-title">Bonjour <?= Renderer::escape($user->displayName()) ?></h1>
    <p class="page-subtitle">Moins de solitude. Plus de lucidité. Meilleures décisions.</p>
</div>

<div class="card">
    <div class="card__body">
        <p>Bienvenue dans votre espace <strong>RESSOURCES</strong>.</p>
        <?php if ($club !== null): ?>
            <p style="color: var(--color-text-muted);">
                Club : <strong><?= Renderer::escape($club->name) ?></strong>
                — accès <span class="badge badge--green">actif</span>
            </p>
        <?php else: ?>
            <p style="color: var(--color-text-muted);">
                Vous êtes connecté en tant qu'<strong>administrateur RESSOURCES</strong>.
            </p>
        <?php endif; ?>

        <hr style="border:none;border-top:1px solid var(--color-border);margin:20px 0;">

        <p style="font-size:14px;color:var(--color-text-muted);">
            La bibliothèque, les formations en streaming et l'assistant IA seront disponibles
            dans les prochaines mises en service. Les fondations (compte sécurisé, accès protégé)
            sont en place.
        </p>
    </div>
</div>
