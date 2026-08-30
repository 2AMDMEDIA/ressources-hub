<?php

use App\Helpers\Renderer;
use App\Models\User;

/**
 * @var list<User> $super_admins
 * @var ?string $current_user_id
 * @var bool $profiler_on
 * @var string $csrf_token
 */
$e = fn(?string $s): string => Renderer::escape((string) $s);
?>
<div class="card" style="margin-bottom:20px;">
    <div class="card__header"><h3 class="card__title">Mode debug (profiling)</h3></div>
    <div class="card__body" style="display:flex;align-items:center;justify-content:space-between;gap:20px;flex-wrap:wrap;">
        <div>
            <p style="margin:0 0 4px;">
                Affiche une barre en bas de page avec le <strong>temps de chargement</strong>, le
                <strong>nombre et le détail des requêtes SQL</strong>, la mémoire et les fichiers inclus.
            </p>
            <p style="margin:0;font-size:13px;color:var(--color-text-muted);">
                Visible <strong>uniquement dans votre navigateur</strong> — jamais pour les visiteurs du site.
                État actuel :
                <?php if ($profiler_on): ?>
                    <span class="badge badge--green">Activé</span>
                <?php else: ?>
                    <span class="badge">Désactivé</span>
                <?php endif; ?>
            </p>
        </div>
        <form method="POST" action="/admin/settings/debug-bar" style="margin:0;">
            <input type="hidden" name="_csrf" value="<?= $e($csrf_token) ?>">
            <?php if ($profiler_on): ?>
                <button type="submit" class="btn btn--danger">Désactiver le mode debug</button>
            <?php else: ?>
                <button type="submit" class="btn btn--primary">Activer le mode debug</button>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="admin-cols">
    <div>
        <div class="card">
            <div class="card__header"><h3 class="card__title">Super-administrateurs · <?= count($super_admins) ?></h3></div>
            <table class="table">
                <thead><tr><th>Nom</th><th>Email</th><th>Dernière connexion</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($super_admins as $sa): ?>
                    <tr>
                        <td>
                            <strong><?= $e($sa->displayName()) ?></strong>
                            <?php if ($sa->id === $current_user_id): ?><span class="badge badge--blue" style="margin-left:6px;">Vous</span><?php endif; ?>
                        </td>
                        <td><?= $e($sa->email) ?></td>
                        <td><?= $sa->lastLoginAt ? $e(date('d/m/Y H:i', strtotime($sa->lastLoginAt))) : '<span style="color:var(--color-text-muted)">jamais</span>' ?></td>
                        <td style="text-align:right;white-space:nowrap;">
                            <details style="display:inline-block;position:relative;">
                                <summary class="btn btn--outline btn--sm" style="list-style:none;">Mot de passe</summary>
                                <form method="POST" action="/admin/users/<?= $e($sa->id) ?>/password" style="margin-top:8px;display:flex;gap:6px;justify-content:flex-end;">
                                    <input type="hidden" name="_csrf" value="<?= $e($csrf_token) ?>">
                                    <input type="password" name="password" placeholder="Nouveau mot de passe" minlength="8" required autocomplete="new-password" style="padding:6px 8px;border:1px solid var(--color-border);border-radius:6px;">
                                    <button type="submit" class="btn btn--primary btn--sm">Définir</button>
                                </form>
                            </details>
                            <?php if ($sa->id !== $current_user_id): ?>
                                <form method="POST" action="/admin/settings/super-admins/<?= $e($sa->id) ?>/remove" style="display:inline;" onsubmit="return confirm('Supprimer le compte super-admin de <?= $e($sa->displayName()) ?> ?');">
                                    <input type="hidden" name="_csrf" value="<?= $e($csrf_token) ?>">
                                    <button type="submit" class="btn btn--danger btn--sm">Supprimer</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div>
        <form method="POST" action="/admin/settings/super-admins" class="card">
            <input type="hidden" name="_csrf" value="<?= $e($csrf_token) ?>">
            <div class="card__header"><h3 class="card__title">Ajouter un super-admin</h3></div>
            <div class="card__body" style="display:flex;flex-direction:column;gap:14px;">
                <div class="grid-2">
                    <label class="field"><span class="field__label">Prénom *</span><input type="text" name="first_name" required></label>
                    <label class="field"><span class="field__label">Nom *</span><input type="text" name="last_name" required></label>
                </div>
                <label class="field"><span class="field__label">Email *</span><input type="email" name="email" required></label>
                <label class="field"><span class="field__label">Mot de passe *</span><input type="password" name="password" required minlength="8" autocomplete="new-password"></label>
                <p style="margin:0;font-size:13px;color:var(--color-text-muted);">Le compte pourra se connecter immédiatement avec cet email et ce mot de passe.</p>
                <div><button type="submit" class="btn btn--primary">Créer le super-admin</button></div>
            </div>
        </form>
    </div>
</div>
