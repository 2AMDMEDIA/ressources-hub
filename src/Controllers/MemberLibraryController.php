<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Middleware\Membership;
use App\Repositories\CategoryRepository;
use App\Repositories\ResourceRepository;

/**
 * Espace membre — bibliothèque : ressources par catégorie + lecture d'une ressource.
 */
final class MemberLibraryController extends BaseController
{
    /** Liste des ressources publiées d'une catégorie (et de ses sous-catégories). */
    public function category(string $slug): void
    {
        Membership::guard();
        $catRepo = new CategoryRepository();
        $category = $catRepo->findBySlug($slug);
        if ($category === null) {
            $this->notFound();
            return;
        }

        // Catégorie + ses sous-catégories
        $ids = [$category->id];
        $children = $category->isTopLevel() ? $catRepo->children($category->id) : [];
        foreach ($children as $c) {
            $ids[] = $c->id;
        }
        $resources = (new ResourceRepository())->listPublishedByCategories($ids);

        $this->renderApp('pages.member.library.category', [
            'title' => $category->name,
            'category' => $category,
            'children' => $children,
            'resources' => $resources,
        ], ['active' => 'category:' . $category->slug, 'page_title' => $category->name]);
    }

    /** Page d'une ressource : lecteur Vimeo ou téléchargement. */
    public function resource(string $id): void
    {
        Membership::guard();
        $resource = (new ResourceRepository())->findById($id);
        if ($resource === null || !$resource->isPublished()) {
            $this->notFound();
            return;
        }
        $category = $resource->categoryId !== null
            ? (new CategoryRepository())->findById($resource->categoryId)
            : null;

        $active = $category !== null ? 'category:' . $category->slug : '';
        $this->renderApp('pages.member.library.resource', [
            'title' => $resource->title,
            'resource' => $resource,
            'category' => $category,
        ], ['active' => $active, 'page_title' => $resource->title]);
    }

    /** Téléchargement protégé d'un fichier (PDF, template…) — hors webroot. */
    public function download(string $id): void
    {
        Membership::guard();
        $resource = (new ResourceRepository())->findById($id);
        if ($resource === null || !$resource->isPublished() || $resource->filePath === null) {
            $this->notFound();
            return;
        }
        $path = $resource->filePath;
        if (!is_file($path)) {
            $this->notFound();
            return;
        }

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($path) ?: 'application/octet-stream';
        $name = $resource->fileName ?: (basename($path));

        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . str_replace('"', '', $name) . '"');
        header('Content-Length: ' . (string) filesize($path));
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, no-store');
        readfile($path);
        exit;
    }

    private function notFound(): void
    {
        http_response_code(404);
        $this->render('pages.errors.404', ['title' => 'Ressource introuvable']);
    }
}
