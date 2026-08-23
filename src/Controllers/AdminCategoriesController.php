<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Bootstrap;
use App\Helpers\Csrf;
use App\Middleware\Auth;
use App\Repositories\CategoryRepository;
use App\Session;
use Ramsey\Uuid\Uuid;

/**
 * Back-office super-admin — arborescence de catégories (profondeur illimitée).
 * Chaque catégorie : titre, description courte, description longue, image
 * miniature (upload, sinon logo par défaut), vidéo d'introduction (ID Vimeo).
 */
final class AdminCategoriesController extends BaseController
{
    private const IMG_MIME = [
        'image/png' => 'png', 'image/jpeg' => 'jpg', 'image/webp' => 'webp', 'image/svg+xml' => 'svg',
    ];

    public function index(): void
    {
        Auth::requireSuperAdmin();
        $this->renderAdmin('pages.admin.categories.index', [
            'title' => 'Catégories',
            'tree' => (new CategoryRepository())->tree(),
        ], 'Catégories');
    }

    /** Page de création d'une catégorie (avec choix du parent). */
    public function showNew(): void
    {
        Auth::requireSuperAdmin();
        $this->renderAdmin('pages.admin.categories.new', [
            'title' => 'Nouvelle catégorie',
            'parents' => (new CategoryRepository())->flatList(),
        ], 'Nouvelle catégorie');
    }

    /** Créer une catégorie (racine ou sous une catégorie parente choisie). */
    public function store(): void
    {
        Auth::requireSuperAdmin();
        Csrf::enforce($this->input('_csrf'));
        $repo = new CategoryRepository();

        $name = $this->input('name');
        if ($name === null) {
            $this->flashError('Le nom de la catégorie est requis.');
            $this->redirect('/admin/categories/new');
        }

        $parentId = $this->input('parent_id');
        if ($parentId !== null && $repo->findById($parentId) === null) {
            $parentId = null;
        }

        $cat = $repo->create((string) $name, $parentId);

        // Propriétés riches + image dès la création.
        $data = [
            'short_description' => $this->input('short_description'),
            'long_description' => $this->input('long_description'),
            'intro_video_id' => $this->input('intro_video_id'),
        ];
        $img = $this->handleImageUpload();
        if ($img !== null) {
            $data['thumbnail_url'] = $img;
        }
        $position = (int) ($this->input('position') ?? '0');
        $repo->update($cat->id, (string) $name, $position, $data);

        $this->flashSuccess('Catégorie créée.');
        $this->redirect('/admin/categories');
    }

    public function edit(string $id): void
    {
        Auth::requireSuperAdmin();
        $repo = new CategoryRepository();
        $category = $repo->findById($id);
        if ($category === null) {
            $this->notFound();
            return;
        }
        // Fil d'ariane (parents)
        $breadcrumb = [];
        $cur = $category->parentId;
        while ($cur !== null) {
            $p = $repo->findById($cur);
            if ($p === null) break;
            array_unshift($breadcrumb, $p);
            $cur = $p->parentId;
        }
        $this->renderAdmin('pages.admin.categories.edit', [
            'title' => 'Éditer ' . $category->name,
            'category' => $category,
            'breadcrumb' => $breadcrumb,
        ], $category->name);
    }

    public function update(string $id): void
    {
        Auth::requireSuperAdmin();
        Csrf::enforce($this->input('_csrf'));
        $repo = new CategoryRepository();
        $category = $repo->findById($id);
        if ($category === null) {
            $this->notFound();
            return;
        }

        $name = $this->input('name') ?? $category->name;
        $position = (int) ($this->input('position') ?? (string) $category->position);
        $data = [
            'short_description' => $this->input('short_description'),
            'long_description' => $this->input('long_description'),
            'intro_video_id' => $this->input('intro_video_id'),
        ];

        $img = $this->handleImageUpload();
        if ($img !== null) {
            // supprimer l'ancienne image uploadée (pas le logo par défaut)
            if ($category->thumbnailUrl && str_starts_with($category->thumbnailUrl, '/uploads/categories/')) {
                $old = Bootstrap::rootPath() . '/public' . $category->thumbnailUrl;
                if (is_file($old)) { @unlink($old); }
            }
            $data['thumbnail_url'] = $img;
        }

        $repo->update($id, $name, $position, $data);
        $this->flashSuccess('Catégorie mise à jour.');
        $this->redirect('/admin/categories');
    }

    public function delete(string $id): void
    {
        Auth::requireSuperAdmin();
        Csrf::enforce($this->input('_csrf'));
        $repo = new CategoryRepository();
        $cat = $repo->findById($id);
        if ($cat === null) {
            $this->redirect('/admin/categories');
        }
        if ($repo->countChildren($id) > 0) {
            $this->flashError('Supprimez d\'abord les sous-catégories de « ' . $cat->name . ' ».');
            $this->redirect('/admin/categories');
        }
        $repo->delete($id);
        $this->flashSuccess('Catégorie supprimée.');
        $this->redirect('/admin/categories');
    }

    // ------------------------------------------------------------------ helpers

    private function handleImageUpload(): ?string
    {
        $f = $_FILES['thumbnail'] ?? null;
        if (!is_array($f) || ($f['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        $tmp = $f['tmp_name'] ?? '';
        if (!is_string($tmp) || $tmp === '' || !is_uploaded_file($tmp)) {
            $this->flashError('Échec de l\'upload de l\'image.');
            return null;
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($tmp);
        if (!isset(self::IMG_MIME[$mime])) {
            $this->flashError('Format d\'image non supporté (PNG, JPEG, WebP, SVG).');
            return null;
        }
        $ext = self::IMG_MIME[$mime];
        $dir = Bootstrap::rootPath() . '/public/uploads/categories';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        $file = Uuid::uuid4()->toString() . '.' . $ext;
        if (!move_uploaded_file($tmp, $dir . '/' . $file)) {
            $this->flashError('Impossible d\'enregistrer l\'image.');
            return null;
        }
        return '/uploads/categories/' . $file;
    }

    private function notFound(): void
    {
        http_response_code(404);
        $this->render('pages.errors.404', ['title' => 'Catégorie introuvable']);
    }

    /** @param array<string,mixed> $data */
    private function renderAdmin(string $view, array $data, string $pageTitle): void
    {
        $this->render($view, layout: 'layouts.admin', data: array_merge($data, [
            'admin' => ['active' => 'categories', 'page_title' => $pageTitle, 'user_name' => (string) Session::get('user_full_name', '')],
        ]));
    }
}
