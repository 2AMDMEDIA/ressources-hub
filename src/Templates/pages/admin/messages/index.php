<?php

use App\Helpers\Renderer;

/** @var list<array<string,mixed>> $messages */
$e = fn(?string $s): string => Renderer::escape((string) $s);
$new = 0;
foreach ($messages as $m) { if (($m['status'] ?? '') === 'new') $new++; }
?>
<div class="card">
    <div class="card__header" style="display:flex;justify-content:space-between;align-items:center;">
        <h3 class="card__title">Messages reçus · <?= count($messages) ?><?= $new > 0 ? ' (' . $new . ' non lu' . ($new > 1 ? 's' : '') . ')' : '' ?></h3>
    </div>
    <?php if (empty($messages)): ?>
        <div class="card__body"><div class="empty-state">
            <div class="empty-state__title">Aucun message</div>
            <div class="empty-state__hint">Les demandes envoyées via le formulaire de contact apparaîtront ici.</div>
        </div></div>
    <?php else: ?>
        <table class="table table--clickable">
            <thead><tr><th>Date</th><th>Club</th><th>Manager</th><th>Email</th><th>Statut</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($messages as $m): ?>
                <tr onclick="location.href='/admin/messages/<?= $e($m['id']) ?>'" style="<?= ($m['status'] ?? '') === 'new' ? 'font-weight:600;' : '' ?>">
                    <td><?= $e(date('d/m/Y H:i', strtotime((string) $m['created_at']))) ?></td>
                    <td><?= $e($m['club'] ?? '') ?: '—' ?></td>
                    <td><?= $e(trim(($m['first_name'] ?? '') . ' ' . ($m['name'] ?? ''))) ?></td>
                    <td><?= $e($m['email'] ?? '') ?></td>
                    <td>
                        <?php if (($m['status'] ?? '') === 'new'): ?>
                            <span class="badge badge--amber">Nouveau</span>
                        <?php else: ?>
                            <span class="badge badge--gray">Lu</span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align:right;"><a href="/admin/messages/<?= $e($m['id']) ?>" class="btn btn--ghost btn--sm">Voir →</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
