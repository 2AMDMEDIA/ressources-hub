<?php

use App\Helpers\Renderer;

/**
 * @var list<array{name:string,applied_at:?string,applied:bool,size:int}> $migrations
 * @var ?string $db_error
 * @var string $csrf_token
 */
$e = fn(?string $s): string => Renderer::escape((string) $s);
$pending = array_values(array_filter($migrations, fn ($m) => !$m['applied']));
?>
<p style="color:var(--color-text-muted);margin:0 0 20px;max-width:680px;">
    Cette page applique les évolutions de schéma en attente. Elle ne rejoue jamais une
    migration déjà passée (suivi via la table <code>schema_migrations</code>) : c'est sûr
    à lancer, y compris sur la base de production.
</p>

<?php if ($db_error !== null): ?>
    <div class="flash flash--error" style="margin-bottom:16px;">Connexion base impossible : <?= $e($db_error) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card__header" style="display:flex;justify-content:space-between;align-items:center;">
        <h3 class="card__title">Migrations</h3>
        <?php if (!empty($pending)): ?>
            <form method="POST" action="/admin/migrations/run" onsubmit="return confirm('Appliquer <?= count($pending) ?> migration(s) en attente ?');">
                <input type="hidden" name="_csrf" value="<?= $e($csrf_token) ?>">
                <button type="submit" class="btn btn--primary btn--sm">Appliquer <?= count($pending) ?> migration(s) en attente</button>
            </form>
        <?php else: ?>
            <span class="badge badge--green">À jour</span>
        <?php endif; ?>
    </div>
    <?php if (empty($migrations)): ?>
        <div class="card__body"><p style="margin:0;color:var(--color-text-muted);">Aucun fichier de migration trouvé.</p></div>
    <?php else: ?>
        <table class="table">
            <thead><tr><th>Migration</th><th>Statut</th><th>Appliquée le</th></tr></thead>
            <tbody>
            <?php foreach ($migrations as $m): ?>
                <tr>
                    <td><code><?= $e($m['name']) ?></code></td>
                    <td>
                        <?php if ($m['applied']): ?>
                            <span class="badge badge--green">Appliquée</span>
                        <?php else: ?>
                            <span class="badge badge--amber">En attente</span>
                        <?php endif; ?>
                    </td>
                    <td><?= $m['applied_at'] ? $e(date('d/m/Y H:i', strtotime($m['applied_at']))) : '<span style="color:var(--color-text-muted)">—</span>' ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
