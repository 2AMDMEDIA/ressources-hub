<?php

use App\Helpers\Renderer;
use App\Models\Category;
use App\Models\Resource;

/**
 * @var Category $category
 * @var list<Category> $children
 * @var list<array{cat:Category,resources:list<Resource>}> $children_blocks
 * @var list<Resource> $own_resources
 * @var list<Category> $breadcrumb
 * @var bool $is_member
 * @var string $csrf_token
 */
$e = fn(?string $s): string => Renderer::escape((string) $s);

/** Rend une grille de cartes ressources. Clic : page ressource si membre, sinon popup de connexion. */
$resourceCards = function (array $resources) use ($e, $is_member): void {
    if (empty($resources)) { return; }
    echo '<div class="prog-res-grid">';
    foreach ($resources as $r) {
        $dur = $r->videoDuration ? (intdiv($r->videoDuration, 60) . ' min') : '';
        $inner = '<div class="prog-res-card__thumb">';
        $inner .= $r->thumbnailUrl ? '<img src="' . $e($r->thumbnailUrl) . '" alt="">' : '<span class="prog-res-card__play">▶</span>';
        if ($dur !== '') { $inner .= '<span class="prog-res-card__dur">' . $e($dur) . '</span>'; }
        $inner .= '</div><div class="prog-res-card__body"><h4>' . $e($r->title) . '</h4>';
        if ($r->description) { $inner .= '<p>' . $e(mb_substr($r->description, 0, 90)) . '</p>'; }
        $inner .= '</div>';
        if ($is_member) {
            echo '<a href="/programmes/ressource/' . $e($r->id) . '" class="prog-res-card">' . $inner . '</a>';
        } else {
            echo '<label for="auth-modal" class="prog-res-card prog-res-card--locked">' . $inner . '</label>';
        }
    }
    echo '</div>';
};
?>
<section class="page-hero">
    <div class="container">
        <p class="breadcrumb">
            <a href="/programmes">Programmes</a>
            <?php foreach ($breadcrumb as $b): ?><span class="breadcrumb__sep">›</span><a href="/programmes/<?= $e($b->slug) ?>"><?= $e($b->name) ?></a><?php endforeach; ?>
            <span class="breadcrumb__sep">›</span><span class="breadcrumb__current"><?= $e($category->name) ?></span>
        </p>
        <h1 class="page-hero__title"><?= $e($category->name) ?></h1>
        <?php if ($category->shortDescription): ?>
            <p class="page-hero__lead"><?= $e($category->shortDescription) ?></p>
        <?php endif; ?>
    </div>
</section>

<section class="section">
    <div class="container">
        <!-- Présentation : vidéo (publique) + description longue -->
        <div class="prog-intro">
            <?php if ($category->introVideoId): ?>
                <div class="prog-intro__video">
                    <div class="video-embed">
                        <iframe src="https://player.vimeo.com/video/<?= $e($category->introVideoId) ?>?dnt=1"
                                frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen
                                title="<?= $e($category->name) ?>"></iframe>
                    </div>
                </div>
            <?php endif; ?>
            <div class="prog-intro__text">
                <h2>Présentation</h2>
                <?php if ($category->longDescription): ?>
                    <p style="white-space:pre-line;"><?= $e($category->longDescription) ?></p>
                <?php else: ?>
                    <p style="color:var(--muted);">Description à venir.</p>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!$is_member && (!empty($own_resources) || !empty($children_blocks))): ?>
            <p class="prog-hint">🔒 Les ressources ci-dessous sont réservées aux membres. <label for="auth-modal" class="prog-hint__link">Connectez-vous</label> pour y accéder.</p>
        <?php endif; ?>

        <?php if (!empty($own_resources)): ?>
            <div class="prog-block">
                <h3 class="prog-block__title">Ressources</h3>
                <?php $resourceCards($own_resources); ?>
            </div>
        <?php endif; ?>

        <?php foreach ($children_blocks as $block): ?>
            <div class="prog-block">
                <?php $tot = (int) ($block['total'] ?? 0); ?>
                <div class="prog-block__head">
                    <h3 class="prog-block__title">
                        <?= $e($block['cat']->name) ?>
                        <span class="prog-block__count"><?= $tot ?> ressource<?= $tot > 1 ? 's' : '' ?></span>
                    </h3>
                    <a href="/programmes/<?= $e($block['cat']->slug) ?>" class="btn btn--outline btn--sm">Voir tout<?= $tot > 3 ? ' (' . $tot . ')' : '' ?></a>
                </div>
                <?php if ($block['cat']->shortDescription): ?>
                    <p style="color:var(--muted);margin:0 0 14px;"><?= $e($block['cat']->shortDescription) ?></p>
                <?php endif; ?>
                <?php if (!empty($block['resources'])): ?>
                    <?php $resourceCards($block['resources']); ?>
                <?php else: ?>
                    <p style="color:var(--muted);">Aucune ressource pour l'instant.</p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <?php if (empty($own_resources) && empty($children_blocks)): ?>
            <p style="color:var(--muted);">Aucune ressource disponible dans cette catégorie pour l'instant.</p>
        <?php endif; ?>
    </div>
</section>

<?php if (!$is_member): ?>
    <!-- Popup d'authentification (CSS pur) -->
    <input type="checkbox" id="auth-modal" class="auth-modal-toggle" hidden>
    <div class="auth-modal">
        <label for="auth-modal" class="auth-modal__backdrop"></label>
        <div class="auth-modal__box">
            <label for="auth-modal" class="auth-modal__close" aria-label="Fermer">×</label>
            <h3>Accès réservé aux membres</h3>
            <p>Connectez-vous pour accéder à cette ressource.</p>
            <form method="POST" action="/login" class="login-form">
                <input type="hidden" name="_csrf" value="<?= $e($csrf_token) ?>">
                <label class="field">
                    <span class="field__label">Email</span>
                    <input type="email" name="email" required autocomplete="email">
                </label>
                <label class="field">
                    <span class="field__label">Mot de passe</span>
                    <input type="password" name="password" required autocomplete="current-password">
                </label>
                <button type="submit" class="btn btn--accent btn--block">Se connecter</button>
                <a href="/forgot-password" class="login-form__forgot">Mot de passe oublié ?</a>
            </form>
        </div>
    </div>
<?php endif; ?>
