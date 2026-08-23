<?php

use App\Helpers\Renderer;

/**
 * @var list<array{id:string,label:string,depth:int,is_child:bool}> $parents
 * @var string $csrf_token
 */
$e = fn(?string $s): string => Renderer::escape((string) $s);
?>
<div class="page-actions">
    <a href="/admin/categories" class="btn btn--ghost btn--sm">← Retour aux catégories</a>
</div>

<form method="POST" action="/admin/categories" enctype="multipart/form-data" class="card" style="max-width:820px;">
    <input type="hidden" name="_csrf" value="<?= $e($csrf_token) ?>">
    <div class="card__header"><h3 class="card__title">Nouvelle catégorie</h3></div>
    <div class="card__body" style="display:flex;flex-direction:column;gap:16px;">
        <div class="grid-2">
            <label class="field">
                <span class="field__label">Titre *</span>
                <input type="text" name="name" required autofocus placeholder="Ex. : Vente">
            </label>
            <label class="field">
                <span class="field__label">Catégorie parente</span>
                <select name="parent_id">
                    <option value="">— Aucune (catégorie racine) —</option>
                    <?php foreach ($parents as $p): ?>
                        <option value="<?= $e($p['id']) ?>"><?= str_repeat('— ', (int) $p['depth']) ?><?= $e($p['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </div>

        <div class="grid-2">
            <label class="field">
                <span class="field__label">Ordre d'affichage</span>
                <input type="number" name="position" value="0">
            </label>
            <label class="field">
                <span class="field__label">Vidéo d'introduction (ID Vimeo)</span>
                <input type="text" name="intro_video_id" placeholder="Ex. : 903112233">
            </label>
        </div>

        <label class="field">
            <span class="field__label">Description courte</span>
            <input type="text" name="short_description" maxlength="500" placeholder="Une phrase d'accroche">
        </label>

        <label class="field">
            <span class="field__label">Description longue</span>
            <textarea name="long_description" rows="4" placeholder="Présentation détaillée…"></textarea>
        </label>

        <div class="field">
            <span class="field__label">Image miniature</span>
            <input type="file" name="thumbnail" accept=".png,.jpg,.jpeg,.webp,.svg">
            <span style="font-size:12px;color:var(--color-text-muted);">Sans image, le logo RESSOURCES sera utilisé par défaut.</span>
        </div>

        <div><button type="submit" class="btn btn--primary">Créer la catégorie</button></div>
    </div>
</form>
