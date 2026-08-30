<?php

use App\Helpers\Renderer;
use App\Models\Expert;

/**
 * @var list<Expert> $founders
 * @var list<Expert> $team
 */
$e = fn(?string $s): string => Renderer::escape((string) $s);
?>
<section class="page-hero">
    <div class="container">
        <p class="eyebrow tx-orange">nos experts</p>
        <h1 class="page-hero__title">Un comité d'experts stratégique externalisé.</h1>
        <p class="page-hero__lead">
            Des professionnels du marché du fitness, mobilisés sur des sujets 100% terrain,
            pour challenger vos pratiques et vous aider à décider au bon moment.
        </p>
    </div>
</section>

<?php if ($founders !== []): ?>
<section class="section">
    <div class="container">
        <div class="founders-list">
            <?php foreach ($founders as $f): ?>
                <div class="founder-block">
                    <?php if ($f->title): ?>
                        <p class="founder-block__title tx-orange"><?= $e($f->title) ?></p>
                    <?php endif; ?>
                    <div class="expert-lead">
                        <div class="expert-lead__avatar expert-lead__avatar--<?= $e($f->accentClass()) ?>" aria-hidden="true">
                            <?php if ($f->hasPhoto()): ?>
                                <img src="<?= $e($f->photoUrl) ?>" alt="<?= $e($f->name) ?>">
                            <?php else: ?>
                                <?= $e($f->initials()) ?>
                            <?php endif; ?>
                        </div>
                        <div class="expert-lead__body">
                            <h2><?= $e($f->name) ?></h2>
                            <?php if ($f->role): ?><p class="expert-lead__role"><?= $e($f->role) ?></p><?php endif; ?>
                            <?php if ($f->bio): ?><p><?= nl2br($e($f->bio)) ?></p><?php endif; ?>
                            <?php if ($f->phone || $f->email): ?>
                                <p class="expert-lead__meta">
                                    <?php if ($f->phone): ?><a href="tel:<?= $e(preg_replace('/\s+/', '', $f->phone)) ?>"><?= $e($f->phone) ?></a><?php endif; ?>
                                    <?php if ($f->phone && $f->email): ?> · <?php endif; ?>
                                    <?php if ($f->email): ?><a href="mailto:<?= $e($f->email) ?>"><?= $e($f->email) ?></a><?php endif; ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($team !== []): ?>
<section class="section">
    <div class="container">
        <p class="eyebrow tx-orange">l'équipe</p>
        <h2 class="section__title">Des consultants qui connaissent le terrain.</h2>
        <div class="experts-grid">
            <?php foreach ($team as $ex): ?>
                <article class="expert-card">
                    <div class="expert-card__avatar expert-card__avatar--<?= $e($ex->accentClass()) ?>" aria-hidden="true">
                        <?php if ($ex->hasPhoto()): ?>
                            <img src="<?= $e($ex->photoUrl) ?>" alt="<?= $e($ex->name) ?>">
                        <?php else: ?>
                            <?= $e($ex->initials()) ?>
                        <?php endif; ?>
                    </div>
                    <h3 class="expert-card__name"><?= $e($ex->name) ?></h3>
                    <?php if ($ex->role): ?><p class="expert-card__role"><?= $e($ex->role) ?></p><?php endif; ?>
                    <?php if ($ex->bio): ?><p class="expert-card__bio"><?= $e($ex->bio) ?></p><?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="cta-band">
    <div class="container cta-band__inner">
        <div>
            <h2>Envie d'échanger avec un expert ?</h2>
            <p>Prenons rendez-vous pour parler de votre club.</p>
        </div>
        <a href="/contact" class="btn btn--accent btn--lg">Nous contacter</a>
    </div>
</section>
