<?php

use App\Helpers\Renderer;
use App\Models\Expert;

/**
 * @var string $mode   'new' | 'edit'
 * @var ?Expert $expert
 * @var string $kind   'founder' | 'team'
 * @var string $csrf_token
 */
$e = fn(?string $s): string => Renderer::escape((string) $s);
$isFounder = $kind === Expert::KIND_FOUNDER;
$action = $mode === 'new' ? '/admin/experts' : '/admin/experts/' . $e($expert->id) . '/update';
$v = fn(string $field, ?string $default = '') => $expert !== null ? $e($expert->$field) : $e($default);
?>
<p style="margin:0 0 16px;"><a href="/admin/experts" class="btn btn--ghost btn--sm">← Retour à la liste</a></p>

<form method="POST" action="<?= $action ?>" enctype="multipart/form-data" class="card" style="max-width:760px;">
    <input type="hidden" name="_csrf" value="<?= $e($csrf_token) ?>">
    <input type="hidden" name="kind" value="<?= $e($kind) ?>">

    <div class="card__header">
        <h3 class="card__title">
            <?= $mode === 'new' ? 'Nouveau' : 'Éditer' ?> —
            <?= $isFounder ? 'Fondateur' : 'Consultant (équipe)' ?>
        </h3>
    </div>

    <div class="card__body" style="display:flex;flex-direction:column;gap:16px;">

        <div class="grid-2">
            <label class="field">
                <span class="field__label">Nom complet *</span>
                <input type="text" name="name" value="<?= $v('name') ?>" required>
            </label>
            <label class="field">
                <span class="field__label">Ordre d'affichage</span>
                <input type="number" name="position" value="<?= $expert !== null ? (int) $expert->position : 0 ?>" min="0">
            </label>
        </div>

        <label class="field">
            <span class="field__label">Rôle / fonction</span>
            <input type="text" name="role" value="<?= $v('role') ?>" placeholder="Ex. : Expert Marketing & Acquisition">
        </label>

        <label class="field">
            <span class="field__label">Biographie</span>
            <textarea name="bio" rows="4" placeholder="Parcours, spécialités…"><?= $expert !== null ? $e($expert->bio) : '' ?></textarea>
        </label>

        <div class="grid-2">
            <label class="field">
                <span class="field__label">Téléphone <?= $isFounder ? '' : '(optionnel)' ?></span>
                <input type="text" name="phone" value="<?= $v('phone') ?>">
            </label>
            <label class="field">
                <span class="field__label">Email <?= $isFounder ? '' : '(optionnel)' ?></span>
                <input type="email" name="email" value="<?= $v('email') ?>">
            </label>
        </div>

        <label class="field">
            <span class="field__label">Couleur de l'avatar (si pas de photo)</span>
            <select name="accent">
                <?php $acc = $expert?->accent ?: 'steel'; foreach (['steel' => 'Gris acier', 'navy' => 'Bleu marine', 'orange' => 'Orange'] as $key => $lbl): ?>
                    <option value="<?= $key ?>" <?= $acc === $key ? 'selected' : '' ?>><?= $lbl ?></option>
                <?php endforeach; ?>
            </select>
        </label>

        <div class="field">
            <span class="field__label">Photo (miniature)</span>
            <?php if ($expert !== null && $expert->hasPhoto()): ?>
                <div style="display:flex;align-items:center;gap:14px;margin-bottom:8px;">
                    <img src="<?= $e($expert->photoUrl) ?>" alt="" style="width:64px;height:64px;border-radius:50%;object-fit:cover;">
                    <label style="display:inline-flex;align-items:center;gap:6px;font-size:13px;color:var(--color-text-muted);">
                        <input type="checkbox" name="remove_photo" value="1"> Supprimer la photo actuelle
                    </label>
                </div>
            <?php endif; ?>
            <input type="file" name="photo" accept="image/png,image/jpeg,image/webp">
            <span style="font-size:12px;color:var(--color-text-muted);">PNG, JPEG ou WebP. Sans photo, un avatar avec les initiales est affiché.</span>
        </div>

        <div style="display:flex;gap:10px;">
            <button type="submit" class="btn btn--primary"><?= $mode === 'new' ? 'Créer' : 'Enregistrer' ?></button>
            <a href="/admin/experts" class="btn btn--ghost">Annuler</a>
        </div>
    </div>
</form>
