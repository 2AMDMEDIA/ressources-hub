<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Csrf;
use App\Middleware\Auth;
use App\Repositories\ContactMessageRepository;
use App\Session;

/**
 * Back-office super-admin — messages reçus via le formulaire de contact public.
 */
final class AdminMessagesController extends BaseController
{
    public function index(): void
    {
        Auth::requireSuperAdmin();
        $messages = (new ContactMessageRepository())->all();
        $this->renderAdmin('pages.admin.messages.index', [
            'title' => 'Messages',
            'messages' => $messages,
        ], 'Messages');
    }

    public function show(string $id): void
    {
        Auth::requireSuperAdmin();
        $repo = new ContactMessageRepository();
        $message = $repo->findById($id);
        if ($message === null) {
            http_response_code(404);
            $this->render('pages.errors.404', ['title' => 'Message introuvable']);
            return;
        }
        // Marquer comme lu à l'ouverture.
        if (($message['status'] ?? 'new') === 'new') {
            $repo->setStatus($id, 'read');
            $message['status'] = 'read';
        }
        $this->renderAdmin('pages.admin.messages.detail', [
            'title' => 'Message',
            'm' => $message,
        ], 'Message');
    }

    public function delete(string $id): void
    {
        Auth::requireSuperAdmin();
        Csrf::enforce($this->input('_csrf'));
        (new ContactMessageRepository())->delete($id);
        $this->flashSuccess('Message supprimé.');
        $this->redirect('/admin/messages');
    }

    /** @param array<string,mixed> $data */
    private function renderAdmin(string $view, array $data, string $pageTitle): void
    {
        $this->render($view, layout: 'layouts.admin', data: array_merge($data, [
            'admin' => ['active' => 'messages', 'page_title' => $pageTitle, 'user_name' => (string) Session::get('user_full_name', '')],
        ]));
    }
}
