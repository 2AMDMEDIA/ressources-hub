<?php

use App\Helpers\Renderer;

/** @var list<array<string,mixed>> $employees */
?>
<div class="card">
    <div class="card__header">
        <h3 class="card__title">Tous les employés · <?= count($employees) ?></h3>
    </div>
    <?php if (empty($employees)): ?>
        <div class="card__body">
            <div class="empty-state">
                <div class="empty-state__title">Aucun employé pour le moment</div>
                <div class="empty-state__hint">Ajoutez des employés depuis la fiche d'un club.</div>
            </div>
        </div>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr><th>Employé</th><th>Fonction</th><th>Email</th><th>Club</th></tr>
            </thead>
            <tbody>
                <?php foreach ($employees as $emp): ?>
                    <tr>
                        <td><strong><?= Renderer::escape(trim($emp['first_name'] . ' ' . $emp['last_name'])) ?></strong></td>
                        <td><?= Renderer::escape($emp['job_title'] ?? '') ?: '<span style="color:var(--color-text-muted)">—</span>' ?></td>
                        <td>
                            <?php if (!empty($emp['email'])): ?>
                                <a href="mailto:<?= Renderer::escape($emp['email']) ?>"><?= Renderer::escape($emp['email']) ?></a>
                            <?php else: ?>
                                <span style="color:var(--color-text-muted)">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="/admin/clubs/<?= Renderer::escape($emp['club_id']) ?>">
                                <?= Renderer::escape($emp['club_name']) ?><?= !empty($emp['club_city']) ? ' <span style="color:var(--color-text-muted);font-size:12px;">· ' . Renderer::escape($emp['club_city']) . '</span>' : '' ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
