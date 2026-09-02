<?php
use App\Helpers\Renderer;

/**
 * Page Tarif — offre de lancement (audit initial + accompagnement).
 * @var array<int,array{0:string,1:string}> $domains
 */
?>
<section class="page-hero">
    <div class="container">
        <p class="eyebrow tx-orange">le tarif</p>
        <h1 class="page-hero__title">Un tarif simple, clair.</h1>
        <p class="page-hero__lead">
            Un audit initial le premier mois pour partir du réel, puis un accompagnement
            continu et des ressources pédagogiques.
        </p>
    </div>
</section>

<section class="section">
    <div class="container">
        <h2 class="offer-launch-title">Offre de lancement</h2>

        <div class="offer-teaser__price" style="max-width:420px;margin:0 auto;">
            <div class="price-badge">
                <span class="price-badge__label">1er mois</span>
                <span class="price-badge__amount">990€</span>
                <span class="price-badge__feature">Audit sur site + Accompagnement + Ressources pédagogiques</span>
            </div>
            <div class="price-badge price-badge--main">
                <span class="price-badge__label">les 11 mois suivants</span>
                <span class="price-badge__amount">390€<span class="price-badge__per">/mois</span></span>
                <span class="price-badge__feature">Accompagnement + Ressources pédagogiques</span>
            </div>
        </div>
        <div style="text-align:center;margin-top:24px;">
            <a href="/contact" class="btn btn--accent btn--lg">Je démarre</a>
        </div>

        <div class="offer-detail">
            <div class="offer-detail__col">
                <h2 class="offer-detail__title"><span class="offer-detail__step">1</span> L'audit initial</h2>
                <ul class="pricing-list">
                    <li>Visio de préparation (2h)</li>
                    <li>Questionnaire en amont</li>
                    <li>Appel client mystère</li>
                    <li>Visite client mystère</li>
                    <li>Audit sur site (8h)</li>
                    <li>Rapport d'analyse complet</li>
                </ul>
            </div>
            <div class="offer-detail__col offer-detail__col--main">
                <h2 class="offer-detail__title"><span class="offer-detail__step">2</span> Accompagnement + Ressources pédagogiques</h2>
                <ul class="pricing-list">
                    <li>1 appel de suivi + une synthèse par mail tous les 15 jours</li>
                    <li>1 visio par mois</li>
                    <li>1 Live formation par mois</li>
                    <li>1 masterclass par trimestre</li>
                    <li>2 évènements par an (1<sup>er</sup> juin 2027)</li>
                    <li>Accès aux ressources pédagogiques</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<section class="section section--tint">
    <div class="container promise">
        <p class="eyebrow tx-orange">la promesse</p>
        <h2 class="section__title">Mieux piloter, mieux décider, mieux performer.</h2>
        <p>Un comité d'experts stratégique externalisé, disponible tout au long de l'année,
           sur tous vos enjeux — de la vente au pilotage, du marketing aux ressources humaines.</p>
        <a href="/contact" class="btn btn--navy btn--lg">Discutons de votre club</a>
    </div>
</section>

<section class="section">
    <div class="container">
        <h2 class="section__title">Ressources : des sujets 100% terrain</h2>
        <div class="domains-grid">
            <?php foreach ($domains as [$name, $desc]): ?>
                <div class="domain">
                    <span class="domain__chev">›</span>
                    <div>
                        <h3 class="domain__name"><?= Renderer::escape($name) ?></h3>
                        <p class="domain__desc"><?= Renderer::escape($desc) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
