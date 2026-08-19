<?php

use App\Helpers\Renderer;

/** @var array<int,array{0:string,1:string}> $domains */
?>
<section class="page-hero">
    <div class="container">
        <p class="eyebrow tx-orange">le dispositif</p>
        <h1 class="page-hero__title">Un cadre vivant, un rythme utile.</h1>
        <p class="page-hero__lead">
            RESSOURCES, ce n'est pas une formation ponctuelle : c'est un accompagnement
            continu, rythmé, pour ne plus décider seul et avancer toute l'année.
        </p>
    </div>
</section>

<section class="section">
    <div class="container">
        <p class="eyebrow tx-orange">le rythme</p>
        <h2 class="section__title">Un accompagnement régulier, toute l'année.</h2>
        <div class="programs-grid">
            <article class="program-card">
                <span class="program-card__tag">Tous les 15 jours</span>
                <h3>Un appel de suivi</h3>
                <p>Un point régulier avec votre référent pour prendre du recul et décider au bon moment.</p>
            </article>
            <article class="program-card">
                <span class="program-card__tag">Tous les 15 jours</span>
                <h3>Une visio ou un live training</h3>
                <p>Des sessions pratiques pour challenger vos pratiques et monter en compétence avec les équipes.</p>
            </article>
            <article class="program-card">
                <span class="program-card__tag">Chaque trimestre</span>
                <h3>Une masterclass</h3>
                <p>Un format long (60 à 90 min) sur un enjeu stratégique du club, animé par un expert du secteur.</p>
            </article>
            <article class="program-card">
                <span class="program-card__tag">2 fois par an</span>
                <h3>Des événements</h3>
                <p>Des rendez-vous pour échanger entre pairs, rompre l'isolement et partager les bonnes pratiques.</p>
            </article>
            <article class="program-card">
                <span class="program-card__tag">En continu</span>
                <h3>Des lives thématiques</h3>
                <p>Des directs réguliers sur les sujets d'actualité du marché du fitness.</p>
            </article>
            <article class="program-card">
                <span class="program-card__tag">24h/24</span>
                <h3>La bibliothèque de ressources</h3>
                <p>Replays, fiches et modèles accessibles à tout moment depuis l'espace membres.</p>
            </article>
        </div>
    </div>
</section>

<section class="section section--tint">
    <div class="container">
        <p class="eyebrow tx-orange">des sujets 100% terrain</p>
        <h2 class="section__title">Tous vos enjeux, couverts par nos experts.</h2>
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

<section class="cta-band">
    <div class="container cta-band__inner">
        <div>
            <h2>Prêt à structurer votre accompagnement ?</h2>
            <p>Découvrez l'offre et démarrons ensemble.</p>
        </div>
        <a href="/prix" class="btn btn--accent btn--lg">Voir le tarif</a>
    </div>
</section>
