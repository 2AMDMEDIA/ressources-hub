<?php

use App\Helpers\Renderer;
use App\Models\Category;

/** @var list<Category> $categories */
$e = fn(?string $s): string => Renderer::escape((string) $s);
?>
<section class="page-hero">
    <div class="container">
        <p class="eyebrow tx-orange">programmes</p>
        <h1 class="page-hero__title">Programmes</h1>
        <p class="page-hero__lead">
            Voici la liste de toutes les ressources disponibles sur RESSOURCES by Fitness Challenges.
        </p>
    </div>
</section>

<section class="section">
    <div class="container">
        <?php if (empty($categories)): ?>
            <p style="color:var(--muted);text-align:center;">Les programmes seront bientôt disponibles.</p>
        <?php else: ?>
            <div class="prog-grid">
                <?php foreach ($categories as $cat): ?>
                    <a href="/programmes/<?= $e($cat->slug) ?>" class="prog-card">
                        <div class="prog-card__img">
                            <img src="<?= $e($cat->thumbnail()) ?>" alt="<?= $e($cat->name) ?>" loading="lazy">
                        </div>
                        <h2 class="prog-card__title"><?= $e($cat->name) ?></h2>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
