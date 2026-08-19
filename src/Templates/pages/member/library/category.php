<?php

use App\Helpers\Renderer;
use App\Models\Category;
use App\Models\Resource;

/**
 * @var Category $category
 * @var list<Category> $children
 * @var list<Resource> $resources
 */
$e = fn(?string $s): string => Renderer::escape((string) $s);
$duration = function (?int $s): string {
    if (!$s) return '';
    $m = intdiv($s, 60);
    return $m >= 1 ? $m . ' min' : $s . ' s';
};
$badgeByFormat = [
    'video' => 'blue', 'replay_live' => 'purple', 'masterclass' => 'amber',
    'pdf' => 'gray', 'template' => 'gray', 'podcast' => 'green',
];
?>
<div class="page-head">
    <h1 class="page-title"><?= $e($category->name) ?></h1>
    <p class="page-subtitle"><?= count($resources) ?> ressource<?= count($resources) > 1 ? 's' : '' ?> disponible<?= count($resources) > 1 ? 's' : '' ?></p>
</div>

<?php if (empty($resources)): ?>
    <div class="card"><div class="card__body"><div class="empty-state">
        <div class="empty-state__title">Aucune ressource pour l'instant</div>
        <div class="empty-state__hint">De nouveaux contenus seront ajoutés dans cette catégorie prochainement.</div>
    </div></div></div>
<?php else: ?>
    <div class="res-grid">
        <?php foreach ($resources as $r): ?>
            <a href="/ressource/<?= $e($r->id) ?>" class="res-card">
                <div class="res-card__thumb">
                    <?php if ($r->thumbnailUrl): ?>
                        <img src="<?= $e($r->thumbnailUrl) ?>" alt="" loading="lazy">
                    <?php else: ?>
                        <span class="res-card__thumb-ph"><?= $r->isVideo() ? '▶' : '📄' ?></span>
                    <?php endif; ?>
                    <?php if ($r->isVideo() && $r->videoDuration): ?>
                        <span class="res-card__duration"><?= $duration($r->videoDuration) ?></span>
                    <?php endif; ?>
                </div>
                <div class="res-card__body">
                    <h3 class="res-card__title"><?= $e($r->title) ?></h3>
                    <?php if ($r->description): ?>
                        <p class="res-card__desc"><?= $e($r->description) ?></p>
                    <?php endif; ?>
                    <div class="res-card__meta">
                        <span class="badge badge--<?= $badgeByFormat[$r->format] ?? 'gray' ?>"><?= $e($r->formatLabel()) ?></span>
                        <?php if ($r->levelLabel()): ?><span class="badge badge--gray"><?= $e($r->levelLabel()) ?></span><?php endif; ?>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
