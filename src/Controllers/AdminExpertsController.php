<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Bootstrap;
use App\Helpers\Csrf;
use App\Middleware\Auth;
use App\Models\Expert;
use App\Repositories\ExpertRepository;
use App\Session;
use Ramsey\Uuid\Uuid;

/**
 * Back-office super-admin — page « Nos experts ».
 * Deux blocs : Fondateurs (kind=founder) et Équipe (kind=team). Mêmes champs.
 * Photo uploadable (sinon avatar à initiales).
 */
final class AdminExpertsController extends BaseController
{
    private const IMG_MIME = [
        'image/png' => 'png', 'image/jpeg' => 'jpg', 'image/webp' => 'webp',
    ];

    public function index(): void
    {
        Auth::requireSuperAdmin();
        $repo = new ExpertRepository();
        $this->renderAdmin('pages.admin.experts.index', [
            'title' => 'Nos experts',
            'founders' => $repo->listByKind(Expert::KIND_FOUNDER),
            'team' => $repo->listByKind(Expert::KIND_TEAM),
        ], 'Nos experts');
    }

    public function showNew(): void
    {
        Auth::requireSuperAdmin();
        $kind = $this->input('kind') === Expert::KIND_FOUNDER ? Expert::KIND_FOUNDER : Expert::KIND_TEAM;
        $this->renderAdmin('pages.admin.experts.form', [
            'title' => $kind === Expert::KIND_FOUNDER ? 'Nouveau fondateur' : 'Nouveau membre de l\'équipe',
            'mode' => 'new',
            'expert' => null,
            'kind' => $kind,
        ], 'Nos experts');
    }

    public function store(): void
    {
        Auth::requireSuperAdmin();
        Csrf::enforce($this->input('_csrf'));

        $data = $this->collect();
        if ($data['name'] === '') {
            $this->flashError('Le nom est requis.');
            $this->redirect('/admin/experts/new?kind=' . $data['kind']);
        }
        $photo = $this->handlePhotoUpload();
        if ($photo !== null) {
            $data['photo_url'] = $photo;
        }
        (new ExpertRepository())->create($data);

        $this->flashSuccess($data['kind'] === Expert::KIND_FOUNDER ? 'Fondateur ajouté.' : 'Membre ajouté.');
        $this->redirect('/admin/experts');
    }

    public function edit(string $id): void
    {
        Auth::requireSuperAdmin();
        $expert = (new ExpertRepository())->findById($id);
        if ($expert === null) {
            $this->notFound();
            return;
        }
        $this->renderAdmin('pages.admin.experts.form', [
            'title' => 'Éditer ' . $expert->name,
            'mode' => 'edit',
            'expert' => $expert,
            'kind' => $expert->kind,
        ], 'Nos experts');
    }

    public function update(string $id): void
    {
        Auth::requireSuperAdmin();
        Csrf::enforce($this->input('_csrf'));
        $repo = new ExpertRepository();
        $expert = $repo->findById($id);
        if ($expert === null) {
            $this->notFound();
            return;
        }

        $data = $this->collect();
        if ($data['name'] === '') {
            $this->flashError('Le nom est requis.');
            $this->redirect('/admin/experts/' . $id . '/edit');
        }

        $photo = $this->handlePhotoUpload();
        if ($photo !== null) {
            // Supprimer l'ancienne photo uploadée.
            if ($expert->photoUrl && str_starts_with($expert->photoUrl, '/uploads/experts/')) {
                $old = Bootstrap::rootPath() . '/public' . $expert->photoUrl;
                if (is_file($old)) { @unlink($old); }
            }
            $data['photo_url'] = $photo;
        } elseif ($this->inputBool('remove_photo')) {
            if ($expert->photoUrl && str_starts_with($expert->photoUrl, '/uploads/experts/')) {
                $old = Bootstrap::rootPath() . '/public' . $expert->photoUrl;
                if (is_file($old)) { @unlink($old); }
            }
            $data['photo_url'] = null;
        }

        $repo->update($id, $data);
        $this->flashSuccess('Modifications enregistrées.');
        $this->redirect('/admin/experts');
    }

    public function delete(string $id): void
    {
        Auth::requireSuperAdmin();
        Csrf::enforce($this->input('_csrf'));
        $repo = new ExpertRepository();
        $expert = $repo->findById($id);
        if ($expert === null) {
            $this->redirect('/admin/experts');
        }
        if ($expert->photoUrl && str_starts_with($expert->photoUrl, '/uploads/experts/')) {
            $old = Bootstrap::rootPath() . '/public' . $expert->photoUrl;
            if (is_file($old)) { @unlink($old); }
        }
        $repo->delete($id);
        $this->flashSuccess('Supprimé.');
        $this->redirect('/admin/experts');
    }

    // ------------------------------------------------------------------ helpers

    /** @return array<string,mixed> */
    private function collect(): array
    {
        return [
            'kind' => $this->input('kind') === Expert::KIND_FOUNDER ? Expert::KIND_FOUNDER : Expert::KIND_TEAM,
            'title' => $this->input('title'),
            'name' => (string) ($this->input('name') ?? ''),
            'role' => $this->input('role'),
            'bio' => $this->input('bio'),
            'phone' => $this->input('phone'),
            'email' => $this->input('email'),
            'accent' => $this->input('accent'),
            'position' => (int) ($this->input('position') ?? '0'),
        ];
    }

    private function handlePhotoUpload(): ?string
    {
        $f = $_FILES['photo'] ?? null;
        if (!is_array($f) || ($f['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        $tmp = $f['tmp_name'] ?? '';
        if (!is_string($tmp) || $tmp === '' || !is_uploaded_file($tmp)) {
            $this->flashError('Échec de l\'upload de la photo.');
            return null;
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($tmp);
        if (!isset(self::IMG_MIME[$mime])) {
            $this->flashError('Format de photo non supporté (PNG, JPEG, WebP).');
            return null;
        }
        $ext = self::IMG_MIME[$mime];
        $dir = Bootstrap::rootPath() . '/public/uploads/experts';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        $file = Uuid::uuid4()->toString() . '.' . $ext;
        if (!move_uploaded_file($tmp, $dir . '/' . $file)) {
            $this->flashError('Impossible d\'enregistrer la photo.');
            return null;
        }
        return '/uploads/experts/' . $file;
    }

    private function notFound(): void
    {
        http_response_code(404);
        $this->render('pages.errors.404', ['title' => 'Expert introuvable']);
    }

    /** @param array<string,mixed> $data */
    private function renderAdmin(string $view, array $data, string $pageTitle): void
    {
        $this->render($view, layout: 'layouts.admin', data: array_merge($data, [
            'admin' => ['active' => 'experts', 'page_title' => $pageTitle, 'user_name' => (string) Session::get('user_full_name', '')],
        ]));
    }
}
