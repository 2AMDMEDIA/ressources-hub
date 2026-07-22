<?php

use App\Helpers\Renderer;

/**
 * @var array<int,array{0:string,1:string}> $domains
 * @var array{name:string,phone:string,email:string,company:string,address:string} $lead
 * @var array<int,array{name:string,role:string,bio:string,initials:string,accent:string}> $experts
 */
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

<section class="section">
    <div class="container">
        <div class="expert-lead">
            <div class="expert-lead__avatar" aria-hidden="true"><?= Renderer::escape(mb_substr($lead['name'], 0, 1)) ?></div>
            <div class="expert-lead__body">
                <h2><?= Renderer::escape($lead['name']) ?></h2>
                <p class="expert-lead__role">Fondateur &amp; référent RESSOURCES — Fitness Challenges</p>
                <p>Plus de 12 ans d'accompagnement des professionnels du fitness en France,
                   Belgique et Suisse. Point de contact privilégié de votre accompagnement.</p>
                <p class="expert-lead__meta">
                    <a href="tel:<?= Renderer::escape('+33676209512') ?>"><?= Renderer::escape($lead['phone']) ?></a>
                    · <a href="mailto:<?= Renderer::escape($lead['email']) ?>"><?= Renderer::escape($lead['email']) ?></a>
                </p>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <p class="eyebrow tx-orange">l'équipe</p>
        <h2 class="section__title">Des consultants qui connaissent le terrain.</h2>
        <div class="experts-grid">
            <?php foreach ($experts as $ex): ?>
                <article class="expert-card">
                    <div class="expert-card__avatar expert-card__avatar--<?= Renderer::escape($ex['accent']) ?>" aria-hidden="true">
                        <?= Renderer::escape($ex['initials']) ?>
                    </div>
                    <h3 class="expert-card__name"><?= Renderer::escape($ex['name']) ?></h3>
                    <p class="expert-card__role"><?= Renderer::escape($ex['role']) ?></p>
                    <p class="expert-card__bio"><?= Renderer::escape($ex['bio']) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
        <p class="note-placeholder">
            <strong>Données fictives :</strong> ces 3 profils sont des exemples de mise en page.
            Ils seront remplacés par la vraie équipe (nom, rôle, photo, parcours) dès réception.
        </p>
    </div>
</section>

<section class="section section--tint">
    <div class="container">
        <p class="eyebrow tx-orange">domaines d'expertise</p>
        <h2 class="section__title">10 domaines, une seule équipe à vos côtés.</h2>
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
            <h2>Envie d'échanger avec un expert ?</h2>
            <p>Prenons rendez-vous pour parler de votre club.</p>
        </div>
        <a href="/contact" class="btn btn--accent btn--lg">Nous contacter</a>
    </div>
</section>
