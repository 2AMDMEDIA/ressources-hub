<?php

use App\Helpers\Renderer;

/**
 * @var array<string,mixed> $m
 * @var string $csrf_token
 */
$e = fn(?string $s): string => Renderer::escape((string) $s);
?>
<div class="page-actions" style="display:flex;justify-content:space-between;align-items:center;">
    <a href="/admin/messages" class="btn btn--ghost btn--sm">← Retour aux messages</a>
    <form method="POST" action="/admin/messages/<?= $e($m['id']) ?>/delete" onsubmit="return confirm('Supprimer ce message ?');">
        <input type="hidden" name="_csrf" value="<?= $e($csrf_token) ?>">
        <button type="submit" class="btn btn--danger btn--sm">Supprimer</button>
    </form>
</div>

<div class="admin-cols">
    <div class="card">
        <div class="card__header"><h3 class="card__title">Message</h3></div>
        <div class="card__body">
            <p style="margin:0;white-space:pre-line;"><?= $e($m['message'] ?? '') ?></p>
        </div>
    </div>

    <div class="card">
        <div class="card__header"><h3 class="card__title">Coordonnées</h3></div>
        <div class="card__body">
            <dl class="club-facts">
                <div><dt>Club</dt><dd><?= $e($m['club'] ?? '—') ?></dd></div>
                <div><dt>Adresse</dt><dd><?= $e($m['club_address'] ?? '—') ?></dd></div>
                <div><dt>Manager</dt><dd><?= $e(trim(($m['first_name'] ?? '') . ' ' . ($m['name'] ?? ''))) ?></dd></div>
                <div><dt>Email</dt><dd><a href="mailto:<?= $e($m['email'] ?? '') ?>"><?= $e($m['email'] ?? '') ?></a></dd></div>
                <div><dt>Téléphone</dt><dd><?= $e($m['phone'] ?? '—') ?></dd></div>
                <div><dt>Reçu le</dt><dd><?= $e(date('d/m/Y à H:i', strtotime((string) $m['created_at']))) ?></dd></div>
            </dl>
            <a href="mailto:<?= $e($m['email'] ?? '') ?>" class="btn btn--primary btn--block" style="margin-top:16px;">Répondre par email</a>
        </div>
    </div>
</div>
