<?php

use App\Helpers\Renderer;
use App\Models\Category;

/**
 * @var list<array{cat:Category,children:list<Category>}> $tree
 * @var string $csrf_token
 */
$e = fn(?string $s): string => Renderer::escape((string) $s);
$csrf = $e($csrf_token);
?>
<p style="color:var(--color-text-muted);margin:0 0 20px;max-width:640px;">
    Organisez la bibliothèque en catégories et sous-catégories (2 niveaux maximum).
    Ces catégories accueilleront ensuite les ressources (vidéos, replays, fiches…).
</p>

<!-- Ajouter une catégorie racine -->
<form method="POST" action="/admin/categories" class="card" style="margin-bottom:24px;">
    <input type="hidden" name="_csrf" value="<?= $csrf ?>">
    <div class="card__body" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">
        <label class="field" style="flex:1;min-width:220px;">
            <span class="field__label">Nouvelle catégorie</span>
            <input type="text" name="name" required placeholder="Ex. : Vente">
        </label>
        <button type="submit" class="btn btn--primary">Ajouter la catégorie</button>
    </div>
</form>

<?php if (empty($tree)): ?>
    <div class="card"><div class="card__body"><div class="empty-state">
        <div class="empty-state__title">Aucune catégorie</div>
        <div class="empty-state__hint">Créez votre première catégorie ci-dessus.</div>
    </div></div></div>
<?php endif; ?>

<?php foreach ($tree as $node): $cat = $node['cat']; ?>
    <div class="card cat-card">
        <div class="card__header cat-head">
            <form method="POST" action="/admin/categories/<?= $e($cat->id) ?>/update" class="cat-form">
                <input type="hidden" name="_csrf" value="<?= $csrf ?>">
                <input type="number" name="position" value="<?= $cat->position ?>" title="Ordre" class="pos-input">
                <input type="text" name="name" value="<?= $e($cat->name) ?>" required class="cat-name-input">
                <button type="submit" class="btn btn--secondary btn--sm">Enregistrer</button>
            </form>
            <form method="POST" action="/admin/categories/<?= $e($cat->id) ?>/delete" onsubmit="return confirm('Supprimer la catégorie « <?= $e($cat->name) ?> » ?');">
                <input type="hidden" name="_csrf" value="<?= $csrf ?>">
                <button type="submit" class="btn btn--danger btn--sm">Supprimer</button>
            </form>
        </div>
        <div class="card__body">
            <?php if (!empty($node['children'])): ?>
                <div class="subcat-list">
                    <?php foreach ($node['children'] as $sub): ?>
                        <div class="subcat-row">
                            <form method="POST" action="/admin/categories/<?= $e($sub->id) ?>/update" class="cat-form">
                                <input type="hidden" name="_csrf" value="<?= $csrf ?>">
                                <input type="number" name="position" value="<?= $sub->position ?>" title="Ordre" class="pos-input">
                                <input type="text" name="name" value="<?= $e($sub->name) ?>" required class="cat-name-input">
                                <button type="submit" class="btn btn--ghost btn--sm">Enreg.</button>
                            </form>
                            <form method="POST" action="/admin/categories/<?= $e($sub->id) ?>/delete" onsubmit="return confirm('Supprimer la sous-catégorie « <?= $e($sub->name) ?> » ?');">
                                <input type="hidden" name="_csrf" value="<?= $csrf ?>">
                                <button type="submit" class="btn btn--danger btn--sm">×</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="/admin/categories/<?= $e($cat->id) ?>/sub" class="subcat-add">
                <input type="hidden" name="_csrf" value="<?= $csrf ?>">
                <input type="text" name="name" required placeholder="Nouvelle sous-catégorie">
                <button type="submit" class="btn btn--primary btn--sm">+ Sous-catégorie</button>
            </form>
        </div>
    </div>
<?php endforeach; ?>
