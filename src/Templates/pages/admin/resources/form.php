<?php

use App\Helpers\Renderer;
use App\Models\Resource;

/**
 * @var string $mode  'new' | 'edit'
 * @var ?Resource $resource
 * @var list<array{id:string,label:string,is_child:bool}> $categories
 * @var string $csrf_token
 */
$e = fn(?string $s): string => Renderer::escape((string) $s);
$r = $resource;
$action = $mode === 'new' ? '/admin/resources' : '/admin/resources/' . $e($r->id) . '/update';
$val = fn(?string $v): string => $e($v);
?>
<div class="page-actions">
    <a href="/admin/resources" class="btn btn--ghost btn--sm">← Retour aux ressources</a>
</div>

<form method="POST" action="<?= $action ?>" enctype="multipart/form-data" class="card" style="max-width:820px;">
    <input type="hidden" name="_csrf" value="<?= $e($csrf_token) ?>">

    <div class="card__body" style="display:flex;flex-direction:column;gap:16px;">
        <label class="field">
            <span class="field__label">Titre *</span>
            <input type="text" name="title" required value="<?= $val($r?->title) ?>" placeholder="Ex. : Réduire les résiliations en 90 jours">
        </label>

        <div class="grid-3">
            <label class="field">
                <span class="field__label">Catégorie</span>
                <select name="category_id">
                    <option value="">— Aucune —</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= $e($c['id']) ?>" <?= ($r?->categoryId ?? '') === $c['id'] ? 'selected' : '' ?>>
                            <?= $c['is_child'] ? '— ' : '' ?><?= $e($c['label']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="field">
                <span class="field__label">Format *</span>
                <select name="format" required>
                    <?php foreach (Resource::FORMATS as $k => $label): ?>
                        <option value="<?= $k ?>" <?= ($r?->format ?? 'video') === $k ? 'selected' : '' ?>><?= $e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="field">
                <span class="field__label">Niveau</span>
                <select name="level">
                    <option value="">—</option>
                    <?php foreach (Resource::LEVELS as $k => $label): ?>
                        <option value="<?= $k ?>" <?= ($r?->level ?? '') === $k ? 'selected' : '' ?>><?= $e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </div>

        <label class="field">
            <span class="field__label">Description</span>
            <textarea name="description" rows="3" placeholder="Résumé du contenu…"><?= $val($r?->description) ?></textarea>
        </label>
    </div>

    <div class="card__header" style="border-top:1px solid var(--color-border);"><h3 class="card__title">Vidéo (Vimeo)</h3></div>
    <div class="card__body" style="display:flex;flex-direction:column;gap:16px;">
        <p style="margin:0;font-size:13px;color:var(--color-text-muted);">Pour les formats vidéo / replay / masterclass. Renseignez l'ID de la vidéo Vimeo (privée, restriction de domaine).</p>
        <div class="grid-3">
            <label class="field">
                <span class="field__label">ID Vimeo</span>
                <input type="text" name="video_id" value="<?= $val($r?->videoId) ?>" placeholder="Ex. : 903112233">
            </label>
            <label class="field">
                <span class="field__label">Durée (secondes)</span>
                <input type="number" name="video_duration" min="0" value="<?= $r?->videoDuration !== null ? (int) $r->videoDuration : '' ?>">
            </label>
            <label class="field">
                <span class="field__label">URL miniature</span>
                <input type="text" name="thumbnail_url" value="<?= $val($r?->thumbnailUrl) ?>">
            </label>
        </div>
    </div>

    <div class="card__header" style="border-top:1px solid var(--color-border);"><h3 class="card__title">Fichier (PDF, template, audio…)</h3></div>
    <div class="card__body" style="display:flex;flex-direction:column;gap:12px;">
        <p style="margin:0;font-size:13px;color:var(--color-text-muted);">Pour les fiches PDF, modèles ou podcasts. Le fichier est stocké hors du dossier public.</p>
        <?php if ($r?->fileName): ?>
            <p style="margin:0;font-size:14px;">Fichier actuel : <strong><?= $e($r->fileName) ?></strong> <span style="color:var(--color-text-muted);">(laisser vide pour le conserver)</span></p>
        <?php endif; ?>
        <input type="file" name="file" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.mp3,.zip">
    </div>

    <div class="card__header" style="border-top:1px solid var(--color-border);"><h3 class="card__title">Publication</h3></div>
    <div class="card__body" style="display:flex;flex-direction:column;gap:16px;">
        <div class="grid-2">
            <label class="field">
                <span class="field__label">Statut</span>
                <select name="status">
                    <option value="draft" <?= ($r?->status ?? 'draft') === 'draft' ? 'selected' : '' ?>>Brouillon</option>
                    <option value="published" <?= ($r?->status ?? '') === 'published' ? 'selected' : '' ?>>Publié</option>
                </select>
            </label>
            <label class="field" style="justify-content:flex-end;">
                <span class="field__label">Coup de projecteur</span>
                <label style="display:flex;align-items:center;gap:8px;font-size:14px;">
                    <input type="checkbox" name="is_spotlight" value="1" <?= $r?->isSpotlight ? 'checked' : '' ?>>
                    Mettre en avant sur le tableau de bord
                </label>
            </label>
        </div>
        <div><button type="submit" class="btn btn--primary"><?= $mode === 'new' ? 'Créer la ressource' : 'Enregistrer' ?></button></div>
    </div>
</form>
