<?php

use App\Helpers\Renderer;

/**
 * @var string $csrf_token
 * @var array<string,mixed> $old
 */
$old = $old ?? [];
$v = fn(string $k, string $d = ''): string => Renderer::escape((string) ($old[$k] ?? $d));
?>
<div class="page-actions">
    <a href="/admin/clubs" class="btn btn--ghost btn--sm">← Retour aux clubs</a>
</div>

<form method="POST" action="/admin/clubs" class="card" style="max-width:720px;">
    <input type="hidden" name="_csrf" value="<?= Renderer::escape($csrf_token) ?>">

    <div class="card__header"><h3 class="card__title">Le club</h3></div>
    <div class="card__body" style="display:flex;flex-direction:column;gap:16px;">
        <label class="field">
            <span class="field__label">Nom du club *</span>
            <input type="text" name="name" required value="<?= $v('name') ?>" placeholder="Ex. : Fitness Park Aix">
        </label>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <label class="field">
                <span class="field__label">Nombre de sièges</span>
                <input type="number" name="seats_limit" min="1" value="<?= $v('seats', '5') ?>">
            </label>
            <label class="field">
                <span class="field__label">Référence contrat</span>
                <input type="text" name="contract_ref" value="<?= $v('contractRef') ?>">
            </label>
        </div>
        <label class="field">
            <span class="field__label">Email de contact du club</span>
            <input type="email" name="contact_email" value="<?= $v('contactEmail') ?>">
        </label>
    </div>

    <div class="card__header" style="border-top:1px solid var(--color-border);"><h3 class="card__title">Le manager (compte propriétaire)</h3></div>
    <div class="card__body" style="display:flex;flex-direction:column;gap:16px;">
        <p style="margin:0;font-size:13px;color:var(--color-text-muted);">
            Le manager recevra un email pour définir son mot de passe et activer son accès.
        </p>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <label class="field">
                <span class="field__label">Nom du manager</span>
                <input type="text" name="owner_name" value="<?= $v('ownerName') ?>">
            </label>
            <label class="field">
                <span class="field__label">Email du manager *</span>
                <input type="email" name="owner_email" required value="<?= $v('ownerEmail') ?>">
            </label>
        </div>
        <div>
            <button type="submit" class="btn btn--primary">Créer le club &amp; inviter le manager</button>
        </div>
    </div>
</form>
