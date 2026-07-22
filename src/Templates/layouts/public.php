<?php

use App\Helpers\Renderer;

/**
 * Layout du site vitrine public.
 *
 * @var string $title
 * @var array{active:string,is_logged_in:bool,user_name:string,login_email:string} $nav
 * @var array<int,array{type:string,message:string}> $flashes
 * @var string $csrf_token
 * @var array{name:string,version:string,url:string} $app
 * @var string $content_html
 */
$brand = function (string $variant = 'light'): string {
    $rColor = '#5C8AAF';
    $txtColor = $variant === 'light' ? '#1A2230' : '#ffffff';
    $subColor = $variant === 'light' ? '#5C8AAF' : '#C8DFF4';
    return '<span class="brand">'
        . '<span class="brand__mark" aria-hidden="true">'
        . '<svg viewBox="0 0 40 40" width="34" height="34"><rect x="1" y="1" width="38" height="38" rx="9" fill="' . $rColor . '"/>'
        . '<path d="M15 12h4.4v2.3c1-1.7 2.6-2.6 4.8-2.6v4.1c-.5-.1-1-.2-1.6-.2-2 0-3.2 1.1-3.2 3.6V28H15z" fill="#fff"/></svg>'
        . '</span>'
        . '<span class="brand__text" style="color:' . $txtColor . '">ressources'
        . '<span class="brand__sub" style="color:' . $subColor . '">by Fitness Challenges</span>'
        . '</span></span>';
};

$links = [
    ['/', 'Accueil', 'home'],
    ['/experts', 'Nos experts', 'experts'],
    ['/prix', 'Prix', 'prix'],
    ['/contact', 'Contact', 'contact'],
];
?><!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Renderer::escape($title) ?></title>
    <meta name="description" content="RESSOURCES by Fitness Challenges — le comité d'experts stratégique externalisé pour les dirigeants, exploitants et managers de clubs de fitness.">
    <link rel="stylesheet" href="/assets/css/site.css">
</head>
<body class="site">
    <header class="site-header">
        <div class="site-header__inner">
            <a href="/" class="site-header__brand"><?= $brand('light') ?></a>

            <input type="checkbox" id="navtoggle" class="navtoggle" hidden>
            <label for="navtoggle" class="navburger" aria-label="Menu">
                <span></span><span></span><span></span>
            </label>

            <nav class="site-nav">
                <?php foreach ($links as [$href, $label, $key]): ?>
                    <a href="<?= $href ?>" class="site-nav__link<?= $nav['active'] === $key ? ' is-active' : '' ?>"><?= Renderer::escape($label) ?></a>
                <?php endforeach; ?>

                <?php if ($nav['is_logged_in']): ?>
                    <a href="/dashboard" class="btn btn--accent btn--sm site-nav__cta">Mon espace</a>
                <?php else: ?>
                    <details class="login-pop">
                        <summary class="btn btn--outline btn--sm">Espace membres</summary>
                        <div class="login-pop__panel">
                            <form method="POST" action="/login" class="login-form">
                                <input type="hidden" name="_csrf" value="<?= Renderer::escape($csrf_token) ?>">
                                <p class="login-form__title">Connexion à l'espace membres</p>
                                <label class="field">
                                    <span class="field__label">Email</span>
                                    <input type="email" name="email" required autocomplete="email" value="<?= Renderer::escape($nav['login_email']) ?>">
                                </label>
                                <label class="field">
                                    <span class="field__label">Mot de passe</span>
                                    <input type="password" name="password" required autocomplete="current-password">
                                </label>
                                <button type="submit" class="btn btn--accent btn--block">Se connecter</button>
                                <a href="/forgot-password" class="login-form__forgot">Mot de passe oublié ?</a>
                            </form>
                        </div>
                    </details>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <?php if (!empty($flashes)): ?>
        <div class="site-flashes">
            <?php foreach ($flashes as $flash): ?>
                <div class="flash flash--<?= Renderer::escape($flash['type']) ?>"><?= Renderer::escape($flash['message']) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <main>
        <?= $content_html ?>
    </main>

    <footer class="site-footer">
        <div class="site-footer__inner">
            <div class="site-footer__brand">
                <?= $brand('dark') ?>
                <p class="site-footer__baseline">Échanger, S'entraîner, Performer.</p>
            </div>
            <nav class="site-footer__nav">
                <?php foreach ($links as [$href, $label]): ?>
                    <a href="<?= $href ?>"><?= Renderer::escape($label) ?></a>
                <?php endforeach; ?>
            </nav>
            <div class="site-footer__contact">
                <p><strong>Bertrand Lataste</strong></p>
                <p><a href="tel:+33676209512">06 76 20 95 12</a></p>
                <p><a href="mailto:ressources@fitness-challenges.com">ressources@fitness-challenges.com</a></p>
                <p>Fitness Challenges<br>730 rue Pierre Simon Laplace, 13290 Aix-en-Provence</p>
            </div>
        </div>
        <div class="site-footer__legal">
            © <?= date('Y') ?> RESSOURCES by Fitness Challenges — MD MEDIA EVENT. Tous droits réservés.
        </div>
    </footer>
</body>
</html>
