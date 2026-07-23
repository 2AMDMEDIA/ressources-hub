<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Bootstrap;
use App\Helpers\Csrf;
use App\Helpers\Str;
use App\Middleware\Auth;
use App\Models\Resource;
use App\Repositories\CategoryRepository;
use App\Repositories\ResourceRepository;
use App\Session;

/**
 * Back-office super-admin — gestionnaire des ressources (contenus) de la bibliothèque.
 */
final class AdminResourcesController extends BaseController
{
    private const STATUSES = ['draft', 'published'];
    private const UPLOAD_MIME = [
        'application/pdf' => 'pdf',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'application/vnd.ms-excel' => 'xls',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
        'application/vnd.ms-powerpoint' => 'ppt',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
        'audio/mpeg' => 'mp3',
        'application/zip' => 'zip',
    ];

    public function index(): void
    {
        Auth::requireSuperAdmin();
        $catRepo = new CategoryRepository();
        $filter = $this->input('category');
        $resources = (new ResourceRepository())->listWithCategory($filter);
        $this->renderAdmin('pages.admin.resources.index', [
            'title' => 'Ressources',
            'resources' => $resources,
            'categories' => $catRepo->flatList(),
            'filter' => $filter,
        ], 'resources', 'Ressources — Contenus');
    }

    public function showNew(): void
    {
        Auth::requireSuperAdmin();
        $this->renderForm('new', null);
    }

    public function create(): void
    {
        Auth::requireSuperAdmin();
        Csrf::enforce($this->input('_csrf'));

        $title = $this->input('title');
        if ($title === null) {
            $this->flashError('Le titre est requis.');
            $this->redirect('/admin/resources/new');
        }
        $format = $this->input('format') ?? 'video';
        if (!array_key_exists($format, Resource::FORMATS)) {
            $format = 'video';
        }

        $data = $this->collect($title, $format);
        $data['slug'] = Str::slug($title);
        $data['created_by'] = Session::userId();

        // Upload fichier (formats non-vidéo)
        $file = $this->handleUpload();
        if ($file !== null) {
            $data['file_path'] = $file['path'];
            $data['file_name'] = $file['name'];
        }

        (new ResourceRepository())->create($data);
        $this->flashSuccess('Ressource créée.');
        $this->redirect('/admin/resources');
    }

    public function edit(string $id): void
    {
        Auth::requireSuperAdmin();
        $resource = (new ResourceRepository())->findById($id);
        if ($resource === null) {
            $this->notFound();
            return;
        }
        $this->renderForm('edit', $resource);
    }

    public function update(string $id): void
    {
        Auth::requireSuperAdmin();
        Csrf::enforce($this->input('_csrf'));
        $repo = new ResourceRepository();
        $resource = $repo->findById($id);
        if ($resource === null) {
            $this->notFound();
            return;
        }

        $title = $this->input('title') ?? $resource->title;
        $format = $this->input('format') ?? $resource->format;
        if (!array_key_exists($format, Resource::FORMATS)) {
            $format = $resource->format;
        }
        $data = $this->collect($title, $format);
        $data['slug'] = $resource->slug ?: Str::slug($title);

        $file = $this->handleUpload();
        if ($file !== null) {
            // supprimer l'ancien fichier
            if ($resource->filePath && is_file($resource->filePath)) {
                @unlink($resource->filePath);
            }
            $data['file_path'] = $file['path'];
            $data['file_name'] = $file['name'];
        }

        $repo->update($id, $data);
        $this->flashSuccess('Ressource mise à jour.');
        $this->redirect('/admin/resources');
    }

    public function toggleStatus(string $id): void
    {
        Auth::requireSuperAdmin();
        Csrf::enforce($this->input('_csrf'));
        $repo = new ResourceRepository();
        $resource = $repo->findById($id);
        if ($resource !== null) {
            $repo->setStatus($id, $resource->isPublished() ? 'draft' : 'published');
            $this->flashSuccess($resource->isPublished() ? 'Ressource dépubliée.' : 'Ressource publiée.');
        }
        $this->redirect('/admin/resources');
    }

    public function toggleSpotlight(string $id): void
    {
        Auth::requireSuperAdmin();
        Csrf::enforce($this->input('_csrf'));
        $repo = new ResourceRepository();
        $resource = $repo->findById($id);
        if ($resource !== null) {
            $repo->setSpotlight($id, !$resource->isSpotlight);
        }
        $this->redirect('/admin/resources');
    }

    public function delete(string $id): void
    {
        Auth::requireSuperAdmin();
        Csrf::enforce($this->input('_csrf'));
        $repo = new ResourceRepository();
        $resource = $repo->findById($id);
        if ($resource !== null) {
            if ($resource->filePath && is_file($resource->filePath)) {
                @unlink($resource->filePath);
            }
            $repo->delete($id);
            $this->flashSuccess('Ressource supprimée.');
        }
        $this->redirect('/admin/resources');
    }

    // ------------------------------------------------------------------ helpers

    /** @return array<string,mixed> */
    private function collect(string $title, string $format): array
    {
        $level = $this->input('level');
        if ($level !== null && !array_key_exists($level, Resource::LEVELS)) {
            $level = null;
        }
        $status = $this->input('status');
        if (!in_array($status, self::STATUSES, true)) {
            $status = 'draft';
        }
        $dur = $this->input('video_duration');
        return [
            'category_id' => $this->input('category_id'),
            'title' => $title,
            'description' => $this->input('description'),
            'format' => $format,
            'level' => $level,
            'video_provider' => $this->input('video_id') !== null ? 'vimeo' : null,
            'video_id' => $this->input('video_id'),
            'video_duration' => ($dur !== null && $dur !== '') ? (int) $dur : null,
            'thumbnail_url' => $this->input('thumbnail_url'),
            'status' => $status,
            'is_spotlight' => $this->inputBool('is_spotlight'),
        ];
    }

    /** @return array{path:string,name:string}|null */
    private function handleUpload(): ?array
    {
        $f = $_FILES['file'] ?? null;
        if (!is_array($f) || ($f['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        $tmp = $f['tmp_name'] ?? '';
        if (!is_string($tmp) || $tmp === '' || !is_uploaded_file($tmp)) {
            $this->flashError('Échec de l\'upload du fichier.');
            return null;
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($tmp);
        if (!isset(self::UPLOAD_MIME[$mime])) {
            $this->flashError('Type de fichier non autorisé (' . $mime . ').');
            return null;
        }
        $ext = self::UPLOAD_MIME[$mime];
        $dir = Bootstrap::rootPath() . '/storage/uploads/resources';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        $id = \Ramsey\Uuid\Uuid::uuid4()->toString();
        $path = $dir . '/' . $id . '.' . $ext;
        if (!move_uploaded_file($tmp, $path)) {
            $this->flashError('Impossible d\'enregistrer le fichier.');
            return null;
        }
        $original = (string) ($f['name'] ?? ('fichier.' . $ext));
        return ['path' => $path, 'name' => $original];
    }

    private function renderForm(string $mode, ?Resource $resource): void
    {
        $this->renderAdmin('pages.admin.resources.form', [
            'title' => $mode === 'new' ? 'Nouvelle ressource' : 'Éditer la ressource',
            'mode' => $mode,
            'resource' => $resource,
            'categories' => (new CategoryRepository())->flatList(),
        ], 'resources', $mode === 'new' ? 'Nouvelle ressource' : 'Éditer la ressource');
    }

    private function notFound(): void
    {
        http_response_code(404);
        $this->render('pages.errors.404', ['title' => 'Ressource introuvable']);
    }

    /** @param array<string,mixed> $data */
    private function renderAdmin(string $view, array $data, string $active, string $pageTitle): void
    {
        $this->render($view, layout: 'layouts.admin', data: array_merge($data, [
            'admin' => ['active' => $active, 'page_title' => $pageTitle, 'user_name' => (string) Session::get('user_full_name', '')],
        ]));
    }
}
