<?php

use App\Helpers\Renderer;
use App\Models\Category;
use App\Models\Resource;

/**
 * @var Resource $resource
 * @var ?Category $category
 * @var list<Category> $breadcrumb
 */
$e = fn(?string $s): string => Renderer::escape((string) $s);
$r = $resource;
?>
<section class="page-hero">
    <div class="container">
        <p class="breadcrumb">
            <a href="/programmes">Programmes</a>
            <?php foreach (($breadcrumb ?? []) as $b): ?><span class="breadcrumb__sep">›</span><a href="/programmes/<?= $e($b->slug) ?>"><?= $e($b->name) ?></a><?php endforeach; ?>
            <span class="breadcrumb__sep">›</span><span class="breadcrumb__current"><?= $e($r->title) ?></span>
        </p>
        <h1 class="page-hero__title"><?= $e($r->title) ?></h1>
    </div>
</section>

<section class="section">
    <div class="container" style="max-width:900px;">
        <?php if ($r->isVideo() && $r->videoId): ?>
            <div class="video-embed" style="margin-bottom:24px;">
                <iframe src="https://player.vimeo.com/video/<?= $e($r->videoId) ?>?dnt=1"
                        frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen
                        title="<?= $e($r->title) ?>"></iframe>
            </div>
        <?php endif; ?>

        <div class="res-meta">
            <span class="badge badge--blue"><?= $e($r->formatLabel()) ?></span>
            <?php if ($r->levelLabel()): ?><span class="badge badge--gray"><?= $e($r->levelLabel()) ?></span><?php endif; ?>
        </div>

        <?php if ($r->description): ?>
            <div class="card" style="margin-top:16px;"><div class="card__body">
                <p style="margin:0;white-space:pre-line;"><?= $e($r->description) ?></p>
            </div></div>
        <?php endif; ?>

        <?php if (!$r->isVideo() && $r->filePath): ?>
            <div class="card" style="margin-top:16px;"><div class="card__body" style="display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;">
                <div>
                    <strong><?= $e($r->fileName ?: 'Document') ?></strong>
                    <div style="color:var(--muted);font-size:13px;">Fichier réservé aux membres</div>
                </div>
                <a href="/ressource/<?= $e($r->id) ?>/download" class="btn btn--accent">Télécharger</a>
            </div></div>
        <?php endif; ?>

        <div style="margin-top:24px;">
            <a href="/programmes/<?= $category !== null ? $e($category->slug) : '' ?>" class="btn btn--outline">← Retour à la catégorie</a>
        </div>
    </div>
</section>
