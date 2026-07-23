<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Csrf;
use App\Middleware\Auth;
use App\Repositories\CategoryRepository;
use App\Session;

/**
 * Back-office super-admin — gestionnaire de catégories / sous-catégories (2 niveaux).
 * Ces catégories accueilleront les ressources de la bibliothèque.
 */
final class AdminCategoriesController extends BaseController
{
    public function index(): void
    {
        Auth::requireSuperAdmin();
        $repo = new CategoryRepository();
        $tree = [];
        foreach ($repo->topLevel() as $cat) {
            $tree[] = ['cat' => $cat, 'children' => $repo->children($cat->id)];
        }
        $this->render('pages.admin.categories.index', layout: 'layouts.admin', data: [
            'title' => 'Ressources',
            'tree' => $tree,
            'admin' => ['active' => 'categories', 'page_title' => 'Ressources — Catégories', 'user_name' => (string) Session::get('user_full_name', '')],
        ]);
    }

    /** Créer une catégorie racine. */
    public function store(): void
    {
        Auth::requireSuperAdmin();
        Csrf::enforce($this->input('_csrf'));
        $name = $this->input('name');
        if ($name === null) {
            $this->flashError('Le nom de la catégorie est requis.');
            $this->redirect('/admin/categories');
        }
        (new CategoryRepository())->create((string) $name, null);
        $this->flashSuccess('Catégorie créée.');
        $this->redirect('/admin/categories');
    }

    /** Créer une sous-catégorie sous un parent. */
    public function storeSub(string $id): void
    {
        Auth::requireSuperAdmin();
        Csrf::enforce($this->input('_csrf'));
        $repo = new CategoryRepository();
        $parent = $repo->findById($id);
        if ($parent === null || !$parent->isTopLevel()) {
            $this->flashError('Catégorie parente invalide (2 niveaux maximum).');
            $this->redirect('/admin/categories');
        }
        $name = $this->input('name');
        if ($name === null) {
            $this->flashError('Le nom de la sous-catégorie est requis.');
            $this->redirect('/admin/categories');
        }
        $repo->create((string) $name, $parent->id);
        $this->flashSuccess('Sous-catégorie créée.');
        $this->redirect('/admin/categories');
    }

    public function update(string $id): void
    {
        Auth::requireSuperAdmin();
        Csrf::enforce($this->input('_csrf'));
        $repo = new CategoryRepository();
        $cat = $repo->findById($id);
        if ($cat === null) {
            $this->redirect('/admin/categories');
        }
        $name = $this->input('name') ?? $cat->name;
        $position = (int) ($this->input('position') ?? (string) $cat->position);
        $repo->update($id, $name, $position);
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
            $this->flashError('Supprimez d\'abord les sous-catégories de « ' . $cat->name .' ».');
            $this->redirect('/admin/categories');
        }
        $repo->delete($id);
        $this->flashSuccess('Catégorie supprimée.');
        $this->redirect('/admin/categories');
    }
}
