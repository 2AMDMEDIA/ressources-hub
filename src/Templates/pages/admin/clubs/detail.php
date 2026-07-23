<?php

use App\Helpers\Renderer;
use App\Models\Club;
use App\Models\User;

/**
 * @var Club $club
 * @var list<User> $members
 * @var int $seats_used
 * @var string $csrf_token
 */
$statusBadge = ['active' => 'green', 'suspended' => 'amber', 'closed' => 'red'];
$statusLabel = ['active' => 'Actif', 'suspended' => 'Suspendu', 'closed' => 'Fermé'];
$roleLabel = ['club_owner' => 'Manager', 'club_member' => 'Collaborateur', 'super_admin' => 'Super-admin'];
$seatsFull = $seats_used >= $club->seatsLimit;
?>
<div class="page-actions" style="display:flex;justify-content:space-between;align-items:center;">
    <a href="/admin/clubs" class="btn btn--ghost btn--sm">← Retour aux clubs</a>
    <span class="badge badge--<?= $statusBadge[$club->status] ?? 'gray' ?>"><?= $statusLabel[$club->status] ?? $club->status ?></span>
</div>

<div class="admin-cols">
    <div>
        <!-- Édition club -->
        <form method="POST" action="/admin/clubs/<?= Renderer::escape($club->id) ?>" class="card">
            <input type="hidden" name="_csrf" value="<?= Renderer::escape($csrf_token) ?>">
            <div class="card__header"><h3 class="card__title">Informations du club</h3></div>
            <div class="card__body" style="display:flex;flex-direction:column;gap:16px;">
                <label class="field">
                    <span class="field__label">Nom du club</span>
                    <input type="text" name="name" value="<?= Renderer::escape($club->name) ?>" required>
                </label>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                    <label class="field">
                        <span class="field__label">Sièges (max)</span>
                        <input type="number" name="seats_limit" min="1" value="<?= (int) $club->seatsLimit ?>">
                    </label>
                    <label class="field">
                        <span class="field__label">Référence contrat</span>
                        <input type="text" name="contract_ref" value="<?= Renderer::escape($club->contractRef ?? '') ?>">
                    </label>
                </div>
                <label class="field">
                    <span class="field__label">Email de contact</span>
                    <input type="email" name="contact_email" value="<?= Renderer::escape($club->contactEmail ?? '') ?>">
                </label>
                <div><button type="submit" class="btn btn--primary">Enregistrer</button></div>
            </div>
        </form>

        <!-- Membres -->
        <div class="card">
            <div class="card__header" style="display:flex;justify-content:space-between;align-items:center;">
                <h3 class="card__title">Membres · <?= $seats_used ?> / <?= (int) $club->seatsLimit ?> sièges</h3>
            </div>
            <table class="table">
                <thead><tr><th>Membre</th><th>Rôle</th><th>Accès</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($members as $m): ?>
                    <tr>
                        <td>
                            <strong><?= Renderer::escape($m->displayName()) ?></strong><br>
                            <span style="color:var(--color-text-muted);font-size:12px;"><?= Renderer::escape($m->email) ?></span>
                        </td>
                        <td><span class="badge badge--<?= $m->isClubOwner() ? 'blue' : 'gray' ?>"><?= $roleLabel[$m->role] ?? $m->role ?></span></td>
                        <td>
                            <?php if ($m->needsPasswordSetup): ?>
                                <span class="badge badge--amber">Invitation en attente</span>
                            <?php elseif ($m->status === 'active'): ?>
                                <span class="badge badge--green">Actif</span>
                            <?php else: ?>
                                <span class="badge badge--red">Suspendu</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align:right;white-space:nowrap;">
                            <?php if ($m->needsPasswordSetup): ?>
                                <form method="POST" action="/admin/clubs/<?= Renderer::escape($club->id) ?>/members/<?= Renderer::escape($m->id) ?>/resend" style="display:inline;">
                                    <input type="hidden" name="_csrf" value="<?= Renderer::escape($csrf_token) ?>">
                                    <button type="submit" class="btn btn--ghost btn--sm">Renvoyer l'invitation</button>
                                </form>
                            <?php endif; ?>
                            <?php if ($m->id !== $club->ownerUserId): ?>
                                <form method="POST" action="/admin/clubs/<?= Renderer::escape($club->id) ?>/members/<?= Renderer::escape($m->id) ?>/remove" style="display:inline;" onsubmit="return confirm('Retirer ce collaborateur du club ?');">
                                    <input type="hidden" name="_csrf" value="<?= Renderer::escape($csrf_token) ?>">
                                    <button type="submit" class="btn btn--danger btn--sm">Retirer</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <div class="card__body" style="border-top:1px solid var(--color-border);">
                <?php if ($seatsFull): ?>
                    <p style="margin:0;font-size:13px;color:#92400e;">Tous les sièges sont occupés (<?= (int) $club->seatsLimit ?>). Augmentez la limite pour inviter un collaborateur.</p>
                <?php else: ?>
                    <form method="POST" action="/admin/clubs/<?= Renderer::escape($club->id) ?>/members" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
                        <input type="hidden" name="_csrf" value="<?= Renderer::escape($csrf_token) ?>">
                        <label class="field" style="flex:1;min-width:140px;">
                            <span class="field__label">Nom</span>
                            <input type="text" name="member_name">
                        </label>
                        <label class="field" style="flex:2;min-width:200px;">
                            <span class="field__label">Email du collaborateur</span>
                            <input type="email" name="member_email" required>
                        </label>
                        <button type="submit" class="btn btn--primary">Inviter</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div>
        <!-- Statut / accès -->
        <div class="card">
            <div class="card__header"><h3 class="card__title">Accès du club</h3></div>
            <div class="card__body" style="display:flex;flex-direction:column;gap:10px;">
                <p style="margin:0 0 6px;font-size:13px;color:var(--color-text-muted);">
                    Suspendre coupe l'accès immédiatement sans supprimer les données.
                </p>
                <?php
                $actions = [
                    'active' => ['Réactiver l\'accès', 'btn--primary'],
                    'suspended' => ['Suspendre l\'accès', 'btn--secondary'],
                    'closed' => ['Fermer le compte', 'btn--danger'],
                ];
                foreach ($actions as $status => [$label, $cls]):
                    if ($status === $club->status) continue;
                ?>
                    <form method="POST" action="/admin/clubs/<?= Renderer::escape($club->id) ?>/status">
                        <input type="hidden" name="_csrf" value="<?= Renderer::escape($csrf_token) ?>">
                        <input type="hidden" name="status" value="<?= $status ?>">
                        <button type="submit" class="btn <?= $cls ?> btn--block"><?= $label ?></button>
                    </form>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Danger -->
        <div class="card">
            <div class="card__header"><h3 class="card__title">Zone de danger</h3></div>
            <div class="card__body">
                <form method="POST" action="/admin/clubs/<?= Renderer::escape($club->id) ?>/delete" onsubmit="return confirm('Supprimer définitivement ce club et TOUS ses comptes ? Cette action est irréversible.');">
                    <input type="hidden" name="_csrf" value="<?= Renderer::escape($csrf_token) ?>">
                    <button type="submit" class="btn btn--danger btn--block">Supprimer le club</button>
                </form>
            </div>
        </div>
    </div>
</div>
