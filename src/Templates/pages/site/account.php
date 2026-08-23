<?php

use App\Helpers\Renderer;
use App\Models\Club;
use App\Models\User;

/**
 * @var User $user
 * @var ?Club $club
 * @var string $csrf_token
 */
$e = fn(?string $s): string => Renderer::escape((string) $s);
$roleLabels = ['super_admin' => 'Administrateur', 'club_owner' => 'Manager', 'club_member' => 'Employé'];
$roleLabel = $roleLabels[$user->role] ?? $user->role;
?>
<section class="page-hero">
    <div class="container">
        <p class="eyebrow tx-orange">mon espace</p>
        <h1 class="page-hero__title">Bonjour <?= $e($user->firstName ?: $user->displayName()) ?></h1>
        <p class="page-hero__lead">Retrouvez ici vos informations.</p>
    </div>
</section>

<section class="section">
    <div class="container" style="max-width:760px;">
        <?php if ($user->isSuperAdmin): ?>
            <div class="account-admin-cta">
                <div>
                    <h2>Espace d'administration</h2>
                    <p>Gérez les clubs, les contenus, les catégories et les messages.</p>
                </div>
                <a href="/admin" target="_blank" rel="noopener" class="btn btn--navy btn--lg">Administrer le site ↗</a>
            </div>
        <?php endif; ?>

        <div class="account-info-card">
            <h2>Mes informations</h2>
            <dl class="account-facts">
                <div><dt>Nom</dt><dd><?= $e($user->displayName()) ?></dd></div>
                <div><dt>Email</dt><dd><?= $e($user->email) ?></dd></div>
                <?php if ($user->jobTitle): ?><div><dt>Fonction</dt><dd><?= $e($user->jobTitle) ?></dd></div><?php endif; ?>
                <div><dt>Rôle</dt><dd><?= $e($roleLabel) ?></dd></div>
                <?php if ($club !== null): ?><div><dt>Club</dt><dd><?= $e($club->name) ?></dd></div><?php endif; ?>
            </dl>
        </div>

        <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:20px;">
            <?php if (!$user->isSuperAdmin): ?>
                <a href="/programmes" class="btn btn--accent">Voir les programmes</a>
            <?php endif; ?>
            <form method="POST" action="/logout" style="margin:0;">
                <input type="hidden" name="_csrf" value="<?= $e($csrf_token) ?>">
                <button type="submit" class="btn btn--outline">Se déconnecter</button>
            </form>
        </div>
    </div>
</section>
