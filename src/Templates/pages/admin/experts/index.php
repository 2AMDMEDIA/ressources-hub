<?php

use App\Helpers\Renderer;
use App\Models\Expert;

/**
 * @var list<Expert> $founders
 * @var list<Expert> $team
 * @var string $csrf_token
 */
$e = fn(?string $s): string => Renderer::escape((string) $s);

/** Rend un tableau de membres (fondateurs ou équipe). */
$rows = function (array $people) use ($e, $csrf_token): void {
    if ($people === []) {
        echo '<p style="color:var(--color-text-muted);margin:0;">Aucune entrée pour l\'instant.</p>';
        return;
    }
    echo '<table class="table"><thead><tr><th style="width:52px;">Ordre</th><th></th><th>Nom</th><th>Rôle</th><th style="text-align:right;"></th></tr></thead><tbody>';
    foreach ($people as $p) {
        echo '<tr>';
        echo '<td>' . (int) $p->position . '</td>';
        echo '<td>';
        if ($p->hasPhoto()) {
            echo '<img src="' . $e($p->photoUrl) . '" alt="" style="width:40px;height:40px;border-radius:50%;object-fit:cover;">';
        } else {
            echo '<span class="expert-mini-avatar expert-mini-avatar--' . $e($p->accentClass()) . '">' . $e($p->initials()) . '</span>';
        }
        echo '</td>';
        echo '<td><strong>' . $e($p->name) . '</strong></td>';
        echo '<td>' . $e($p->role) . '</td>';
        echo '<td style="text-align:right;white-space:nowrap;">';
        echo '<a href="/admin/experts/' . $e($p->id) . '/edit" class="btn btn--outline btn--sm">Éditer</a> ';
        echo '<form method="POST" action="/admin/experts/' . $e($p->id) . '/delete" style="display:inline;" onsubmit="return confirm(\'Supprimer ' . $e($p->name) . ' ?\');">';
        echo '<input type="hidden" name="_csrf" value="' . $e($csrf_token) . '">';
        echo '<button type="submit" class="btn btn--danger btn--sm">Supprimer</button>';
        echo '</form>';
        echo '</td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
};
?>
<div class="card" style="margin-bottom:20px;">
    <div class="card__header" style="display:flex;align-items:center;justify-content:space-between;gap:12px;">
        <h3 class="card__title">Fondateurs · <?= count($founders) ?></h3>
        <a href="/admin/experts/new?kind=founder" class="btn btn--primary btn--sm">+ Ajouter un fondateur</a>
    </div>
    <div class="card__body">
        <p style="margin:0 0 14px;font-size:13px;color:var(--color-text-muted);">
            Bloc du haut de la page « Nos experts ». Vous pouvez ajouter plusieurs fondateurs (mêmes champs que l'équipe).
        </p>
        <?php $rows($founders); ?>
    </div>
</div>

<div class="card">
    <div class="card__header" style="display:flex;align-items:center;justify-content:space-between;gap:12px;">
        <h3 class="card__title">L'équipe (consultants) · <?= count($team) ?></h3>
        <a href="/admin/experts/new?kind=team" class="btn btn--primary btn--sm">+ Ajouter un consultant</a>
    </div>
    <div class="card__body">
        <p style="margin:0 0 14px;font-size:13px;color:var(--color-text-muted);">
            Grille de cartes en bas de la page. Les miniatures sont des photos que vous pouvez uploader.
        </p>
        <?php $rows($team); ?>
    </div>
</div>
