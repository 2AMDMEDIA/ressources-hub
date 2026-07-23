<?php

use App\Helpers\Renderer;
use App\Models\Resource;

/**
 * @var list<array<string,mixed>> $resources
 * @var list<array{id:string,label:string,is_child:bool}> $categories
 * @var ?string $filter
 * @var string $csrf_token
 */
$e = fn(?string $s): string => Renderer::escape((string) $s);
?>
<div class="page-actions" style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
    <form method="GET" action="/admin/resources" style="display:flex;gap:8px;align-items:center;">
        <select name="category" onchange="this.form.submit()" style="padding:8px 10px;border:1px solid var(--color-border);border-radius:8px;">
            <option value="">Toutes les catégories</option>
            <?php foreach ($categories as $c): ?>
                <option value="<?= $e($c['id']) ?>" <?= ($filter ?? '') === $c['id'] ? 'selected' : '' ?>>
                    <?= $c['is_child'] ? '— ' : '' ?><?= $e($c['label']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
    <a href="/admin/resources/new" class="btn btn--primary">+ Nouvelle ressource</a>
</div>

<div class="card">
    <?php if (empty($resources)): ?>
        <div class="card__body"><div class="empty-state">
            <div class="empty-state__title">Aucune ressource</div>
            <div class="empty-state__hint">Ajoutez votre premier contenu avec « + Nouvelle ressource ».</div>
        </div></div>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr><th>Titre</th><th>Catégorie</th><th>Format</th><th>Niveau</th><th>Statut</th><th></th></tr>
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
    <?php endif; ?>
</div>
