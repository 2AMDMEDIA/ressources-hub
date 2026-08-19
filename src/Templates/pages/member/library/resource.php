<?php

use App\Helpers\Renderer;
use App\Models\Category;
use App\Models\Resource;

/**
 * @var Resource $resource
 * @var ?Category $category
 */
$e = fn(?string $s): string => Renderer::escape((string) $s);
$r = $resource;
?>
<div class="page-actions">
    <?php if ($category !== null): ?>
        <a href="/ressources/<?= $e($category->slug) ?>" class="btn btn--ghost btn--sm">← <?= $e($category->name) ?></a>
    <?php else: ?>
        <a href="/dashboard" class="btn btn--ghost btn--sm">← Tableau de bord</a>
    <?php endif; ?>
</div>

<div class="res-detail">
    <?php if ($r->isVideo() && $r->videoId): ?>
        <div class="video-embed">
            <iframe
                src="https://player.vimeo.com/video/<?= $e($r->videoId) ?>?dnt=1"
                frameborder="0"
                allow="autoplay; fullscreen; picture-in-picture"
                allowfullscreen
                title="<?= $e($r->title) ?>"></iframe>
        </div>
    <?php elseif ($r->isVideo()): ?>
        <div class="card"><div class="card__body">
            <p style="margin:0;color:var(--color-text-muted);">La vidéo n'est pas encore disponible.</p>
        </div></div>
    <?php endif; ?>

    <div class="res-detail__head">
        <div class="res-detail__meta">
            <span class="badge badge--blue"><?= $e($r->formatLabel()) ?></span>
            <?php if ($r->levelLabel()): ?><span class="badge badge--gray"><?= $e($r->levelLabel()) ?></span><?php endif; ?>
            <?php if ($category !== null): ?><span class="res-detail__cat"><?= $e($category->name) ?></span><?php endif; ?>
        </div>
        <h1 class="res-detail__title"><?= $e($r->title) ?></h1>
    </div>

    <?php if ($r->description): ?>
        <div class="card"><div class="card__body">
            <p style="margin:0;white-space:pre-line;"><?= $e($r->description) ?></p>
        </div></div>
    <?php endif; ?>

    <?php if (!$r->isVideo() && $r->filePath): ?>
        <div class="card"><div class="card__body" style="display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;">
            <div>
                <strong><?= $e($r->fileName ?: 'Document') ?></strong>
                <div style="color:var(--color-text-muted);font-size:13px;">Fichier réservé aux membres</div>
            </div>
            <a href="/ressource/<?= $e($r->id) ?>/download" class="btn btn--accent">Télécharger</a>
        </div></div>
    <?php endif; ?>
</div>
