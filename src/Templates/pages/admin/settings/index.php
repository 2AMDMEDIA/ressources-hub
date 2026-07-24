<?php

use App\Helpers\Renderer;
use App\Models\User;

/**
 * @var list<User> $super_admins
 * @var ?string $current_user_id
 * @var string $csrf_token
 */
$e = fn(?string $s): string => Renderer::escape((string) $s);
?>
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
                        <td style="text-align:right;">
                            <?php if ($sa->id !== $current_user_id): ?>
                                <form method="POST" action="/admin/settings/super-admins/<?= $e($sa->id) ?>/remove" style="display:inline;" onsubmit="return confirm('Supprimer le compte super-admin de <?= $e($sa->displayName()) ?> ?');">
                                    <input type="hidden" name="_csrf" value="<?= $e($csrf_token) ?>">
                                    <button type="submit" class="btn btn--danger btn--sm">Supprimer</button>
                                </form>
                            <?php else: ?>
                                <span style="color:var(--color-text-muted);font-size:13px;">—</span>
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
