<?php

use App\Helpers\Renderer;

/** @var array<int,array{0:string,1:string}> $domains */
?>
<section class="hero">
    <div class="hero__inner">
        <p class="eyebrow">by Fitness Challenges</p>
        <h1 class="hero__title">Moins de solitude,<br>plus de lucidité,<br><span class="tx-orange">meilleures décisions.</span></h1>
        <p class="hero__lead">
            Le comité d'experts stratégique externalisé pour les dirigeants, exploitants,
            managers et équipes terrain des clubs de fitness.
        </p>
        <div class="hero__actions">
            <a href="/prix" class="btn btn--accent btn--lg">Découvrir l'offre</a>
            <a href="/contact" class="btn btn--outline btn--lg">Nous contacter</a>
        </div>
        <p class="hero__audience">
            <span class="chev">›</span> Pour les <strong>dirigeants</strong> de clubs, les <strong>exploitants</strong>,
            les <strong>managers</strong> et les <strong>équipes terrain</strong>.
        </p>
    </div>
</section>

<section class="section">
    <div class="container">
        <p class="eyebrow tx-orange">le constat</p>
        <h2 class="section__title">Diriger un club est devenu plus exigeant.</h2>
        <div class="cards-3">
            <article class="card-b">
                <h3>Un métier sous pression</h3>
                <p>Marché en mutation, pression sur les marges, complexité opérationnelle,
                   décisions permanentes.</p>
            </article>
            <article class="card-b card-b--orange">
                <h3>Le vrai problème : décider seul…</h3>
                <p>Trop d'injonctions, trop de contenus, pas assez de recul, peu d'espaces
                   pour être challengé.</p>
            </article>
            <article class="card-b card-b--navy">
                <h3>Ce que RESSOURCES vous apporte</h3>
                <p>Rompre l'isolement, redonner de la clarté, challenger les pratiques,
                   aider à décider au bon moment.</p>
            </article>
        </div>
    </div>
</section>

<section class="section section--tint">
    <div class="container offer-teaser">
        <div class="offer-teaser__text">
            <p class="eyebrow tx-orange">l'offre</p>
            <h2 class="section__title">Une offre simple, claire.</h2>
            <p>Un audit initial pour partir du réel, puis un suivi, des conseils et un
               support permanent tout au long de l'année.</p>
            <a href="/prix" class="btn btn--navy btn--lg">Voir le détail des tarifs</a>
        </div>
        <div class="offer-teaser__price">
            <div class="price-badge">
                <span class="price-badge__label">1er mois</span>
                <span class="price-badge__amount">490€</span>
                <span class="price-badge__strike">au lieu de 990€</span>
            </div>
            <div class="price-badge price-badge--main">
                <span class="price-badge__label">puis</span>
                <span class="price-badge__amount">290€</span>
                <span class="price-badge__unit">par mois</span>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <p class="eyebrow tx-orange">des sujets 100% terrain</p>
        <h2 class="section__title">Un comité d'experts sur tous vos enjeux.</h2>
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
            <h2>Mieux piloter, mieux décider, mieux performer.</h2>
            <p>Parlons de votre club et de vos enjeux.</p>
        </div>
        <a href="/contact" class="btn btn--accent btn--lg">Prendre contact</a>
    </div>
</section>
