<?php

use App\Helpers\Renderer;
use App\Models\Category;
use App\Models\Resource;

/**
 * @var list<array<string,mixed>> $resources
 * @var list<array{id:string,label:string,is_child:bool}> $categories
 * @var list<array{cat:Category,children:array}> $tree
 * @var array<string,int> $counts
 * @var ?string $filter
 * @var string $csrf_token
 */
$e = fn(?string $s): string => Renderer::escape((string) $s);

/** Rendu récursif de l'arborescence complète des catégories (profondeur illimitée). */
$renderTree = function (array $nodes) use (&$renderTree, $e, $counts, $filter): void {
    echo '<ul class="cat-tree">';
    foreach ($nodes as $node) {
        /** @var Category $cat */
        $cat = $node['cat'];
        $n = $counts[$cat->id] ?? 0;
        $active = ($filter ?? '') === $cat->id;
        echo '<li class="cat-tree__item">';
        echo '<a href="/admin/resources?category=' . $e($cat->id) . '" class="cat-tree__link' . ($active ? ' is-active' : '') . '">';
        echo '<span class="cat-tree__name">' . $e($cat->name) . '</span>';
        echo '<span class="cat-tree__count">' . $n . '</span>';
        echo '</a>';
        if (!empty($node['children'])) {
            $renderTree($node['children']);
        }
        echo '</li>';
    }
    echo '</ul>';
};
?>
<div class="page-actions" style="display:flex;justify-content:flex-end;align-items:center;gap:12px;margin-bottom:16px;">
    <a href="/admin/resources/new" class="btn btn--primary">+ Nouvelle ressource</a>
</div>

<div class="res-layout">
    <!-- Colonne gauche : arborescence complète des catégories -->
    <aside class="res-tree card">
        <div class="card__header"><h3 class="card__title">Catégories</h3></div>
        <div class="card__body">
            <a href="/admin/resources" class="cat-tree__all<?= ($filter ?? '') === '' ? ' is-active' : '' ?>">
                Toutes les ressources
            </a>
            <?php if (empty($tree)): ?>
                <p style="color:var(--color-text-muted);margin:12px 0 0;font-size:13px;">Aucune catégorie. <a href="/admin/categories">En créer</a>.</p>
            <?php else: ?>
                <?php $renderTree($tree); ?>
            <?php endif; ?>
        </div>
    </aside>

    <!-- Colonne droite : liste des ressources -->
    <div class="res-list card">
        <?php if (empty($resources)): ?>
            <div class="card__body"><div class="empty-state">
                <div class="empty-state__title">Aucune ressource<?= ($filter ?? '') !== '' ? ' dans cette catégorie' : '' ?></div>
                <div class="empty-state__hint">Ajoutez un contenu avec « + Nouvelle ressource ».</div>
            </div></div>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr><th>Titre</th><th>Catégorie</th><th>Format</th><th>ID Vimeo</th><th>Niveau</th><th>Statut</th><th></th></tr>
                    <tr class="table-filters">
                        <th><input type="text" data-col="0" placeholder="Rechercher un titre…"></th>
                        <th><input type="text" data-col="1" placeholder="Catégorie…"></th>
                        <th><input type="text" data-col="2" placeholder="Format…"></th>
                        <th><input type="text" data-col="3" placeholder="ID Vimeo…"></th>
                        <th><input type="text" data-col="4" placeholder="Niveau…"></th>
                        <th><input type="text" data-col="5" placeholder="Statut…"></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($resources as $r): ?>
                    <tr>
                        <td>
                            <a href="/admin/resources/<?= $e($r['id']) ?>/edit"><strong><?= $e($r['title']) ?></strong></a>
                            <?php if (!empty($r['is_spotlight'])): ?><span class="badge badge--amber" style="margin-left:6px;">★ En avant</span><?php endif; ?>
                        </td>
                        <td><?= $e($r['category_name'] ?? '') ?: '<span style="color:var(--color-text-muted)">—</span>' ?></td>
                        <td><?= $e(Resource::FORMATS[$r['format']] ?? $r['format']) ?></td>
                        <td>
                            <?php if (!empty($r['video_id'])): ?>
                                <code style="font-size:13px;"><?= $e($r['video_id']) ?></code>
                            <?php else: ?>
                                <span style="color:var(--color-text-muted)">—</span>
                            <?php endif; ?>
                        </td>
                        <td><?= !empty($r['level']) ? $e(Resource::LEVELS[$r['level']] ?? $r['level']) : '<span style="color:var(--color-text-muted)">—</span>' ?></td>
                        <td>
                            <?php if (($r['status'] ?? 'draft') === 'published'): ?>
                                <span class="badge badge--green">Publié</span>
                            <?php else: ?>
                                <span class="badge badge--gray">Brouillon</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align:right;white-space:nowrap;">
                            <a href="/admin/resources/<?= $e($r['id']) ?>/edit" class="btn btn--ghost btn--sm">Éditer</a>
                            <form method="POST" action="/admin/resources/<?= $e($r['id']) ?>/toggle-status" style="display:inline;">
                                <input type="hidden" name="_csrf" value="<?= $e($csrf_token) ?>">
                                <button type="submit" class="btn btn--secondary btn--sm"><?= ($r['status'] ?? 'draft') === 'published' ? 'Dépublier' : 'Publier' ?></button>
                            </form>
                            <form method="POST" action="/admin/resources/<?= $e($r['id']) ?>/delete" style="display:inline;" onsubmit="return confirm('Supprimer « <?= $e($r['title']) ?> » ?');">
                                <input type="hidden" name="_csrf" value="<?= $e($csrf_token) ?>">
                                <button type="submit" class="btn btn--danger btn--sm">×</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <p class="table-noresult" style="display:none;padding:16px;color:var(--color-text-muted);">Aucune ressource ne correspond à votre recherche.</p>
        <?php endif; ?>
    </div>
</div>

<script>
(function () {
    var filters = document.querySelectorAll('.table-filters input');
    var rows = document.querySelectorAll('.res-list tbody tr');
    var noResult = document.querySelector('.table-noresult');
    if (!filters.length || !rows.length) { return; }
    function apply() {
        var active = [];
        filters.forEach(function (inp) {
            var v = inp.value.trim().toLowerCase();
            if (v) { active.push({ col: parseInt(inp.dataset.col, 10), val: v }); }
        });
        var visible = 0;
        rows.forEach(function (tr) {
            var show = active.every(function (o) {
                var cell = tr.cells[o.col];
                return cell && cell.textContent.toLowerCase().indexOf(o.val) > -1;
            });
            tr.style.display = show ? '' : 'none';
            if (show) { visible++; }
        });
        if (noResult) { noResult.style.display = visible === 0 ? 'block' : 'none'; }
    }
    filters.forEach(function (inp) { inp.addEventListener('input', apply); });
})();
</script>
