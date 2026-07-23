<?php

use App\Helpers\Renderer;
use App\Models\Club;
use App\Models\User;

/**
 * @var Club $club
 * @var ?User $manager
 * @var string $csrf_token
 */
$statusBadge = ['active' => 'green', 'suspended' => 'amber', 'closed' => 'red'];
$statusLabel = ['active' => 'Actif', 'suspended' => 'Suspendu', 'closed' => 'Fermé'];
$e = fn(?string $s): string => Renderer::escape((string) $s);
?>
<div class="page-actions" style="display:flex;justify-content:space-between;align-items:center;">
    <a href="/admin/clubs" class="btn btn--ghost btn--sm">← Retour aux clubs</a>
    <span class="badge badge--<?= $statusBadge[$club->status] ?? 'gray' ?>"><?= $statusLabel[$club->status] ?? $club->status ?></span>
</div>

<div class="admin-cols">
    <div>
        <!-- Club -->
        <form method="POST" action="/admin/clubs/<?= $e($club->id) ?>" class="card">
            <input type="hidden" name="_csrf" value="<?= $e($csrf_token) ?>">
            <div class="card__header"><h3 class="card__title">Établissement</h3></div>
            <div class="card__body" style="display:flex;flex-direction:column;gap:16px;">
                <div class="grid-2">
                    <label class="field">
                        <span class="field__label">Nom du club</span>
                        <input type="text" name="name" required value="<?= $e($club->name) ?>">
                    </label>
                    <label class="field">
                        <span class="field__label">SIRET</span>
                        <input type="text" name="siret" maxlength="14" value="<?= $e($club->siret) ?>">
                    </label>
                </div>
                <label class="field">
                    <span class="field__label">Adresse</span>
                    <input type="text" name="address" value="<?= $e($club->address) ?>">
                </label>
                <div class="grid-3">
                    <label class="field">
                        <span class="field__label">Code postal</span>
                        <input type="text" name="postal_code" value="<?= $e($club->postalCode) ?>">
                    </label>
                    <label class="field">
                        <span class="field__label">Ville</span>
                        <input type="text" name="city" value="<?= $e($club->city) ?>">
                    </label>
                    <label class="field">
                        <span class="field__label">Pays</span>
                        <input type="text" name="country" value="<?= $e($club->country) ?>">
                    </label>
                </div>
                <div class="grid-2">
                    <label class="field">
                        <span class="field__label">Superficie (m²)</span>
                        <input type="number" name="area_sqm" min="0" value="<?= $club->areaSqm !== null ? (int) $club->areaSqm : '' ?>">
                    </label>
                    <label class="field">
                        <span class="field__label">Année d'ouverture</span>
                        <input type="number" name="opening_year" min="1900" max="2100" value="<?= $club->openingYear !== null ? (int) $club->openingYear : '' ?>">
                    </label>
                </div>
                <div><button type="submit" class="btn btn--primary">Enregistrer l'établissement</button></div>
            </div>
        </form>

        <!-- Manager -->
        <form method="POST" action="/admin/clubs/<?= $e($club->id) ?>/manager" class="card">
            <input type="hidden" name="_csrf" value="<?= $e($csrf_token) ?>">
            <div class="card__header" style="display:flex;justify-content:space-between;align-items:center;">
                <h3 class="card__title">Manager</h3>
                <?php if ($manager !== null && $manager->needsPasswordSetup): ?>
                    <span class="badge badge--amber">Invitation en attente</span>
                <?php elseif ($manager !== null): ?>
                    <span class="badge badge--green">Actif</span>
                <?php endif; ?>
            </div>
            <?php if ($manager === null): ?>
                <div class="card__body"><p style="margin:0;color:var(--color-text-muted);">Aucun manager rattaché.</p></div>
            <?php else: ?>
                <div class="card__body" style="display:flex;flex-direction:column;gap:16px;">
                    <div class="grid-2">
                        <label class="field">
                            <span class="field__label">Prénom</span>
                            <input type="text" name="manager_first_name" required value="<?= $e($manager->firstName) ?>">
                        </label>
                        <label class="field">
                            <span class="field__label">Nom</span>
                            <input type="text" name="manager_last_name" required value="<?= $e($manager->lastName) ?>">
                        </label>
                    </div>
                    <div class="grid-2">
                        <label class="field">
                            <span class="field__label">Email</span>
                            <input type="email" name="manager_email" required value="<?= $e($manager->email) ?>">
                        </label>
                        <label class="field">
                            <span class="field__label">Fonction</span>
                            <input type="text" name="manager_job_title" value="<?= $e($manager->jobTitle) ?>">
                        </label>
                    </div>
                    <div style="display:flex;gap:10px;flex-wrap:wrap;">
                        <button type="submit" class="btn btn--primary">Enregistrer le manager</button>
                    </div>
                </div>
            <?php endif; ?>
        </form>

        <?php if ($manager !== null && $manager->needsPasswordSetup): ?>
            <form method="POST" action="/admin/clubs/<?= $e($club->id) ?>/resend" style="margin-top:-8px;">
                <input type="hidden" name="_csrf" value="<?= $e($csrf_token) ?>">
                <button type="submit" class="btn btn--secondary btn--sm">Renvoyer l'invitation au manager</button>
            </form>
        <?php endif; ?>
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
                    <form method="POST" action="/admin/clubs/<?= $e($club->id) ?>/status">
                        <input type="hidden" name="_csrf" value="<?= $e($csrf_token) ?>">
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
                <form method="POST" action="/admin/clubs/<?= $e($club->id) ?>/delete" onsubmit="return confirm('Supprimer définitivement ce club et son compte manager ? Action irréversible.');">
                    <input type="hidden" name="_csrf" value="<?= $e($csrf_token) ?>">
                    <button type="submit" class="btn btn--danger btn--block">Supprimer le club</button>
                </form>
            </div>
        </div>
    </div>
</div>
