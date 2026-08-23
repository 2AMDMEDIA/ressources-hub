<?php

use App\Helpers\Renderer;
use App\Models\Category;

/**
 * @var Category $category
 * @var list<Category> $breadcrumb
 * @var string $csrf_token
 */
$e = fn(?string $s): string => Renderer::escape((string) $s);
$c = $category;
?>
<div class="page-actions">
    <a href="/admin/categories" class="btn btn--ghost btn--sm">← Retour aux catégories</a>
</div>

<?php if (!empty($breadcrumb)): ?>
    <p style="color:var(--color-text-muted);font-size:13px;margin:0 0 12px;">
        <?php foreach ($breadcrumb as $b): ?><?= $e($b->name) ?> › <?php endforeach; ?><strong><?= $e($c->name) ?></strong>
    </p>
<?php endif; ?>

<form method="POST" action="/admin/categories/<?= $e($c->id) ?>/update" enctype="multipart/form-data" class="card" style="max-width:820px;">
    <input type="hidden" name="_csrf" value="<?= $e($csrf_token) ?>">
    <div class="card__header"><h3 class="card__title">Propriétés de la catégorie</h3></div>
    <div class="card__body" style="display:flex;flex-direction:column;gap:16px;">
        <div class="grid-2">
            <label class="field">
                <span class="field__label">Titre *</span>
                <input type="text" name="name" required value="<?= $e($c->name) ?>">
            </label>
            <label class="field">
                <span class="field__label">Ordre d'affichage</span>
                <input type="number" name="position" value="<?= (int) $c->position ?>">
            </label>
        </div>

        <label class="field">
            <span class="field__label">Description courte</span>
            <input type="text" name="short_description" maxlength="500" value="<?= $e($c->shortDescription) ?>" placeholder="Une phrase d'accroche">
        </label>

        <label class="field">
            <span class="field__label">Description longue</span>
            <textarea name="long_description" rows="5" placeholder="Présentation détaillée de la catégorie…"><?= $e($c->longDescription) ?></textarea>
        </label>

        <div class="grid-2">
            <div class="field">
                <span class="field__label">Image miniature</span>
                <div class="cat-thumb-preview">
                    <img src="<?= $e($c->thumbnail()) ?>" alt="">
                    <?php if (!$c->thumbnailUrl): ?><span class="cat-thumb-note">Logo RESSOURCES (par défaut)</span><?php endif; ?>
                </div>
                <input type="file" name="thumbnail" accept=".png,.jpg,.jpeg,.webp,.svg">
                <span style="font-size:12px;color:var(--color-text-muted);">Laisser vide = garder l'image actuelle. Sans image, le logo RESSOURCES est utilisé.</span>
            </div>
            <label class="field">
                <span class="field__label">Vidéo d'introduction (ID Vimeo)</span>
                <input type="text" name="intro_video_id" value="<?= $e($c->introVideoId) ?>" placeholder="Ex. : 903112233">
                <span style="font-size:12px;color:var(--color-text-muted);">Le numéro dans l'URL vimeo.com/<strong>ID</strong>.</span>
            </label>
        </div>

        <div class="form-footer">
            <a href="/admin/categories" class="btn btn--secondary">Retour</a>
            <button type="submit" class="btn btn--primary">Enregistrer</button>
        </div>
    </div>
</form>
