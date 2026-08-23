<?php

use App\Helpers\Renderer;
use App\Models\Category;

/**
 * @var list<array{cat:Category,children:array}> $tree
 * @var string $csrf_token
 */
$e = fn(?string $s): string => Renderer::escape((string) $s);
$csrf = $e($csrf_token);

$renderNode = function (array $node, int $depth) use (&$renderNode, $e, $csrf): void {
    $cat = $node['cat'];
    ?>
    <div class="cat-node" style="margin-left:<?= $depth * 22 ?>px">
        <div class="cat-row">
            <span class="cat-row__pos" title="Ordre d'affichage"><?= (int) $cat->position ?></span>
            <img src="<?= $e($cat->thumbnail()) ?>" class="cat-row__thumb" alt="">
            <a href="/admin/categories/<?= $e($cat->id) ?>/edit" class="cat-row__name"><?= $e($cat->name) ?></a>
            <div class="cat-row__actions">
                <a href="/admin/categories/<?= $e($cat->id) ?>/edit" class="btn btn--secondary btn--sm">Éditer</a>
                <form method="POST" action="/admin/categories/<?= $e($cat->id) ?>/delete" style="display:inline;" onsubmit="return confirm('Supprimer « <?= $e($cat->name) ?> » ?');">
                    <input type="hidden" name="_csrf" value="<?= $csrf ?>">
                    <button type="submit" class="btn btn--danger btn--sm">×</button>
                </form>
            </div>
        </div>
        <?php foreach ($node['children'] as $child) { $renderNode($child, $depth + 1); } ?>
    </div>
    <?php
};
?>
<div class="page-actions" style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
    <p style="color:var(--color-text-muted);margin:0;max-width:560px;">
        Arborescence des catégories (profondeur illimitée). Cliquez sur une catégorie pour éditer
        ses propriétés (titre, descriptions, image, vidéo d'introduction).
    </p>
    <a href="/admin/categories/new" class="btn btn--primary">+ Créer une catégorie</a>
</div>

<div class="card">
    <div class="card__body">
        <?php if (empty($tree)): ?>
            <div class="empty-state">
                <div class="empty-state__title">Aucune catégorie</div>
                <div class="empty-state__hint">Créez votre première catégorie ci-dessus.</div>
            </div>
        <?php else: ?>
            <?php foreach ($tree as $node) { $renderNode($node, 0); } ?>
        <?php endif; ?>
    </div>
</div>
