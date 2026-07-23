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
?>
<div class="page-head">
    <h1 class="page-title">Mon compte</h1>
    <p class="page-subtitle">Mettez à jour vos informations personnelles.</p>
</div>

<div class="account-cols">
    <form method="POST" action="/compte" class="card">
        <input type="hidden" name="_csrf" value="<?= $e($csrf_token) ?>">
        <div class="card__header"><h3 class="card__title">Mes informations</h3></div>
        <div class="card__body" style="display:flex;flex-direction:column;gap:16px;">
            <div class="grid-2">
                <label class="field">
                    <span class="field__label">Prénom *</span>
                    <input type="text" name="first_name" required value="<?= $e($user->firstName) ?>">
                </label>
                <label class="field">
                    <span class="field__label">Nom *</span>
                    <input type="text" name="last_name" required value="<?= $e($user->lastName) ?>">
                </label>
            </div>
            <div class="grid-2">
                <label class="field">
                    <span class="field__label">Email *</span>
                    <input type="email" name="email" required value="<?= $e($user->email) ?>">
                </label>
                <label class="field">
                    <span class="field__label">Fonction</span>
                    <input type="text" name="job_title" value="<?= $e($user->jobTitle) ?>" placeholder="Ex. : Directeur">
                </label>
            </div>
            <div>
                <button type="submit" class="btn btn--accent">Enregistrer</button>
                <a href="/forgot-password" style="margin-left:12px;font-size:14px;">Changer mon mot de passe</a>
            </div>
        </div>
    </form>

    <?php if ($club !== null): ?>
        <div class="card">
            <div class="card__header"><h3 class="card__title">Mon club</h3></div>
            <div class="card__body">
                <p style="margin:0 0 12px;font-size:13px;color:var(--color-text-muted);">
                    Ces informations sont gérées par l'équipe RESSOURCES.
                </p>
                <dl class="club-facts">
                    <div><dt>Nom</dt><dd><?= $e($club->name) ?></dd></div>
                    <?php if ($club->city): ?><div><dt>Ville</dt><dd><?= $e($club->postalCode) ?> <?= $e($club->city) ?></dd></div><?php endif; ?>
                    <?php if ($club->address): ?><div><dt>Adresse</dt><dd><?= $e($club->address) ?></dd></div><?php endif; ?>
                    <?php if ($club->areaSqm): ?><div><dt>Superficie</dt><dd><?= (int) $club->areaSqm ?> m²</dd></div><?php endif; ?>
                    <?php if ($club->openingYear): ?><div><dt>Ouverture</dt><dd><?= (int) $club->openingYear ?></dd></div><?php endif; ?>
                </dl>
            </div>
        </div>
    <?php endif; ?>
</div>
