<?php

use App\Helpers\Renderer;
use App\Models\Club;
use App\Models\Employee;

/**
 * @var ?Club $club
 * @var list<Employee> $employees
 * @var string $csrf_token
 */
$e = fn(?string $s): string => Renderer::escape((string) $s);
?>
<div class="page-head">
    <h1 class="page-title">Employés</h1>
    <p class="page-subtitle">L'équipe de votre club<?= $club !== null ? ' — ' . $e($club->name) : '' ?>.</p>
</div>

<div class="account-cols">
    <!-- Liste -->
    <div class="card">
        <div class="card__header"><h3 class="card__title">Mon équipe · <?= count($employees) ?></h3></div>
        <?php if (empty($employees)): ?>
            <div class="card__body">
                <div class="empty-state">
                    <div class="empty-state__title">Aucun employé pour le moment</div>
                    <div class="empty-state__hint">Ajoutez votre premier employé avec le formulaire ci-contre.</div>
                </div>
            </div>
        <?php else: ?>
            <table class="table">
                <thead><tr><th>Employé</th><th>Fonction</th><th>Accès</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($employees as $emp): ?>
                    <tr>
                        <td>
                            <strong><?= $e($emp->fullName()) ?></strong>
                            <?php if ($emp->email): ?><br><span style="color:var(--color-text-muted);font-size:12px;"><?= $e($emp->email) ?></span><?php endif; ?>
                        </td>
                        <td><?= $e($emp->jobTitle) ?: '<span style="color:var(--color-text-muted)">—</span>' ?></td>
                        <td>
                            <?php if ($emp->hasAccess()): ?>
                                <span class="badge badge--green">A un accès</span>
                            <?php elseif ($emp->email): ?>
                                <form method="POST" action="/employes/<?= $e($emp->id) ?>/grant-access" style="display:inline;" onsubmit="return confirm('Envoyer un accès à <?= $e($emp->fullName()) ?> ?');">
                                    <input type="hidden" name="_csrf" value="<?= $e($csrf_token) ?>">
                                    <button type="submit" class="btn btn--secondary btn--sm">Donner un accès</button>
                                </form>
                            <?php else: ?>
                                <span style="color:var(--color-text-muted);font-size:12px;">email requis</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align:right;">
                            <form method="POST" action="/employes/<?= $e($emp->id) ?>/delete" style="display:inline;" onsubmit="return confirm('Retirer <?= $e($emp->fullName()) ?> ?');">
                                <input type="hidden" name="_csrf" value="<?= $e($csrf_token) ?>">
                                <button type="submit" class="btn btn--danger btn--sm">Retirer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Formulaire de création -->
    <div class="card">
        <div class="card__header"><h3 class="card__title">Ajouter un employé</h3></div>
        <div class="card__body">
            <?php if ($club === null): ?>
                <p style="margin:0;color:var(--color-text-muted);">Aucun club n'est rattaché à votre compte.</p>
            <?php else: ?>
                <form method="POST" action="/employes" style="display:flex;flex-direction:column;gap:14px;">
                    <input type="hidden" name="_csrf" value="<?= $e($csrf_token) ?>">
                    <div class="grid-2">
                        <label class="field"><span class="field__label">Prénom *</span><input type="text" name="first_name" required></label>
                        <label class="field"><span class="field__label">Nom *</span><input type="text" name="last_name" required></label>
                    </div>
                    <label class="field"><span class="field__label">Fonction</span><input type="text" name="job_title" placeholder="Ex. : Coach"></label>
                    <label class="field"><span class="field__label">Email</span><input type="email" name="email"></label>
                    <div><button type="submit" class="btn btn--accent">Ajouter l'employé</button></div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>
