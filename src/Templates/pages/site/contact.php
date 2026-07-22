<?php

use App\Helpers\Renderer;

/**
 * @var array{name:string,phone:string,phone_link:string,email:string,company:string,address:string} $contact
 * @var bool $sent
 * @var array<string,string> $old
 * @var string $csrf_token
 */
$old = $old ?? [];
$v = fn(string $k): string => Renderer::escape($old[$k] ?? '');
?>
<section class="page-hero">
    <div class="container">
        <p class="eyebrow tx-orange">contact</p>
        <h1 class="page-hero__title">Parlons de votre club.</h1>
        <p class="page-hero__lead">
            Une question sur l'offre, l'audit ou l'accompagnement ? Laissez-nous un message,
            nous vous recontactons rapidement.
        </p>
    </div>
</section>

<section class="section">
    <div class="container contact-grid">
        <div class="contact-form-wrap">
            <?php if ($sent): ?>
                <div class="contact-success">
                    <h2>Merci !</h2>
                    <p>Votre message a bien été envoyé. L'équipe RESSOURCES vous recontacte au plus vite.</p>
                    <a href="/" class="btn btn--navy">Retour à l'accueil</a>
                </div>
            <?php else: ?>
                <form method="POST" action="/contact" class="contact-form">
                    <input type="hidden" name="_csrf" value="<?= Renderer::escape($csrf_token) ?>">
                    <!-- Honeypot anti-spam : ne pas remplir -->
                    <div class="hp"><label>Ne pas remplir<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>

                    <div class="form-row">
                        <label class="field">
                            <span class="field__label">Nom *</span>
                            <input type="text" name="name" required value="<?= $v('name') ?>">
                        </label>
                        <label class="field">
                            <span class="field__label">Email *</span>
                            <input type="email" name="email" required value="<?= $v('email') ?>">
                        </label>
                    </div>
                    <div class="form-row">
                        <label class="field">
                            <span class="field__label">Téléphone</span>
                            <input type="tel" name="phone" value="<?= $v('phone') ?>">
                        </label>
                        <label class="field">
                            <span class="field__label">Votre club</span>
                            <input type="text" name="club" value="<?= $v('club') ?>">
                        </label>
                    </div>
                    <label class="field">
                        <span class="field__label">Sujet</span>
                        <input type="text" name="subject" value="<?= $v('subject') ?>" placeholder="Ex. : demande d'information sur l'audit">
                    </label>
                    <label class="field">
                        <span class="field__label">Message *</span>
                        <textarea name="message" rows="6" required placeholder="Décrivez votre besoin en quelques lignes…"><?= $v('message') ?></textarea>
                    </label>
                    <button type="submit" class="btn btn--accent btn--lg">Envoyer le message</button>
                </form>
            <?php endif; ?>
        </div>

        <aside class="contact-info">
            <h2>Coordonnées</h2>
            <p class="contact-info__name"><?= Renderer::escape($contact['name']) ?></p>
            <ul>
                <li><span>Téléphone</span><a href="tel:<?= Renderer::escape($contact['phone_link']) ?>"><?= Renderer::escape($contact['phone']) ?></a></li>
                <li><span>Email</span><a href="mailto:<?= Renderer::escape($contact['email']) ?>"><?= Renderer::escape($contact['email']) ?></a></li>
                <li><span>Adresse</span><?= Renderer::escape($contact['company']) ?><br><?= Renderer::escape($contact['address']) ?></li>
            </ul>
        </aside>
    </div>
</section>
