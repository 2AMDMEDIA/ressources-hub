<?php

use App\Helpers\Renderer;

/**
 * @var string $csrf_token
 * @var array<string,mixed> $old
 */
$old = $old ?? [];
$v = fn(string $k): string => Renderer::escape((string) ($old[$k] ?? ''));
?>
<div class="page-actions">
    <a href="/admin/clubs" class="btn btn--ghost btn--sm">← Retour aux clubs</a>
</div>

<form method="POST" action="/admin/clubs" class="card" style="max-width:760px;">
    <input type="hidden" name="_csrf" value="<?= Renderer::escape($csrf_token) ?>">

    <div class="card__header"><h3 class="card__title">Le club</h3></div>
    <div class="card__body" style="display:flex;flex-direction:column;gap:16px;">
        <div class="grid-2">
            <label class="field">
                <span class="field__label">Nom du club *</span>
                <input type="text" name="name" required value="<?= $v('name') ?>" placeholder="Ex. : Fitness Park Aix">
            </label>
            <label class="field">
                <span class="field__label">SIRET</span>
                <input type="text" name="siret" value="<?= $v('siret') ?>" maxlength="14" placeholder="14 chiffres">
            </label>
        </div>
        <label class="field">
            <span class="field__label">Adresse</span>
            <input type="text" name="address" value="<?= $v('address') ?>">
        </label>
        <div class="grid-3">
            <label class="field">
                <span class="field__label">Code postal</span>
                <input type="text" name="postal_code" value="<?= $v('postal_code') ?>">
            </label>
            <label class="field">
                <span class="field__label">Ville</span>
                <input type="text" name="city" value="<?= $v('city') ?>">
            </label>
            <?php $country = (string) ($old['country'] ?? '') ?: 'France'; ?>
            <label class="field">
                <span class="field__label">Pays</span>
                <input type="text" name="country" value="<?= Renderer::escape($country) ?>">
            </label>
        </div>
        <div class="grid-2">
            <label class="field">
                <span class="field__label">Superficie (m²)</span>
                <input type="number" name="area_sqm" min="0" value="<?= $v('area_sqm') ?>">
            </label>
            <label class="field">
                <span class="field__label">Année d'ouverture</span>
                <input type="number" name="opening_year" min="1900" max="2100" value="<?= $v('opening_year') ?>">
            </label>
        </div>
    </div>

    <div class="card__header" style="border-top:1px solid var(--color-border);"><h3 class="card__title">Le manager</h3></div>
    <div class="card__body" style="display:flex;flex-direction:column;gap:16px;">
        <p style="margin:0;font-size:13px;color:var(--color-text-muted);">
            Le manager recevra un email pour définir son mot de passe et activer son accès.
        </p>
        <div class="grid-2">
            <label class="field">
                <span class="field__label">Prénom *</span>
                <input type="text" name="manager_first_name" required value="<?= $v('manager_first_name') ?>">
            </label>
            <label class="field">
                <span class="field__label">Nom *</span>
                <input type="text" name="manager_last_name" required value="<?= $v('manager_last_name') ?>">
            </label>
        </div>
        <div class="grid-2">
            <label class="field">
                <span class="field__label">Email *</span>
                <input type="email" name="manager_email" required value="<?= $v('manager_email') ?>">
            </label>
            <label class="field">
                <span class="field__label">Fonction</span>
                <input type="text" name="manager_job_title" value="<?= $v('manager_job_title') ?>" placeholder="Ex. : Directeur">
            </label>
        </div>
        <div><button type="submit" class="btn btn--primary">Créer le club &amp; inviter le manager</button></div>
    </div>
</form>
