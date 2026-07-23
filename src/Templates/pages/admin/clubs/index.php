<?php

use App\Helpers\Renderer;

/** @var list<array<string,mixed>> $clubs */
$badge = ['active' => 'green', 'suspended' => 'amber', 'closed' => 'red'];
$statusLabel = ['active' => 'Actif', 'suspended' => 'Suspendu', 'closed' => 'Fermé'];
?>
<div class="page-actions">
    <a href="/admin/clubs/new" class="btn btn--primary">+ Nouveau club</a>
</div>

<div class="card">
    <?php if (empty($clubs)): ?>
        <div class="card__body">
            <div class="empty-state">
                <div class="empty-state__title">Aucun club pour le moment</div>
                <div class="empty-state__hint">Créez votre premier club abonné pour ouvrir un accès.</div>
            </div>
        </div>
    <?php else: ?>
        <table class="table table--clickable">
            <thead>
                <tr>
                    <th>Club</th>
                    <th>Ville</th>
                    <th>Manager</th>
                    <th>Statut</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clubs as $c): ?>
                    <?php $st = $c['status']; ?>
                    <tr onclick="location.href='/admin/clubs/<?= Renderer::escape($c['id']) ?>'">
                        <td><strong><?= Renderer::escape($c['name']) ?></strong></td>
                        <td><?= Renderer::escape($c['city'] ?? '') ?: '<span style="color:var(--color-text-muted)">—</span>' ?></td>
                        <td>
                            <?php if (!empty($c['manager_email'])): ?>
                                <?= Renderer::escape($c['manager_name'] ?: $c['manager_email']) ?><br>
                                <span style="color:var(--color-text-muted);font-size:12px;"><?= Renderer::escape($c['manager_email']) ?></span>
                            <?php else: ?>
                                <span style="color:var(--color-text-muted);">—</span>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge badge--<?= $badge[$st] ?? 'gray' ?>"><?= $statusLabel[$st] ?? $st ?></span></td>
                        <td style="text-align:right;"><a href="/admin/clubs/<?= Renderer::escape($c['id']) ?>" class="btn btn--ghost btn--sm">Gérer →</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
