<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Csrf;
use App\Helpers\Renderer;
use App\Repositories\CategoryRepository;
use App\Repositories\ClubRepository;
use App\Repositories\ContactMessageRepository;
use App\Repositories\ResourceRepository;
use App\Repositories\UserRepository;
use App\Services\Mailer;
use App\Session;

/**
 * Site vitrine public RESSOURCES (accessible à tous) : Accueil, Nos experts,
 * Prix, Contact. Le formulaire de connexion à l'espace membres est dans le
 * header (coin haut droit) et poste vers /login (AuthController).
 */
final class SiteController extends BaseController
{
    private const CONTACT = [
        'name' => 'Bertrand Lataste',
        'phone' => '06 76 20 95 12',
        'phone_link' => '+33676209512',
        'email' => 'ressources@fitness-challenges.com',
        'company' => 'Fitness Challenges',
        'address' => '730 rue Pierre Simon Laplace, 13290 Aix-en-Provence',
    ];

    /** Les 10 domaines « 100% terrain » de la plaquette. */
    private const DOMAINS = [
        ['HOSPITALITY & ACCUEIL', 'Qualité de service et expérience membre au quotidien.'],
        ['RH & MANAGEMENT', 'Les enjeux humains et managériaux au cœur de la performance.'],
        ['PROSPECTION & CLOSING', 'Structurer et fiabiliser le parcours commercial.'],
        ['LEVIERS DE CROISSANCE ADDITIONNELLE', 'Développer le panier moyen et les revenus complémentaires.'],
        ['EXPÉRIENCES & SERVICES SPORTIFS', 'L\'expérience sportive au service de la fidélisation client.'],
        ['COMMUNICATION & MARKETING', 'Positionnement communication locale et campagnes annuelles.'],
        ['RÉFÉRENCEMENT & TUNNELS DE VENTE', 'Acquisition, process et optimisation des leads.'],
        ['PILOTER LE CLUB & KPI', 'Indicateurs clés, tableaux de bord et rentabilité.'],
        ['ANTICIPER DEMAIN', 'Tendances marché, veille et opportunités de croissance.'],
        ['CRÉATION', 'Ouverture de club, concept et business plan.'],
    ];

    /**
     * Consultants — DONNÉES FICTIVES à remplacer par la vraie équipe.
     * initials + accent servent à générer l'avatar tant qu'il n'y a pas de photo.
     */
    private const EXPERTS = [
        [
            'name' => 'Camille Roussel',
            'role' => 'Experte Vente & Développement commercial',
            'bio' => 'Ancienne directrice de réseau, elle structure les process de vente et la montée en compétence des équipes terrain.',
            'initials' => 'CR',
            'accent' => 'steel',
        ],
        [
            'name' => 'Thomas Bianchi',
            'role' => 'Expert Marketing & Acquisition',
            'bio' => 'Spécialiste de la communication locale et de l\'acquisition, il aide les clubs à remplir durablement leur pipeline de prospects.',
            'initials' => 'TB',
            'accent' => 'navy',
        ],
        [
            'name' => 'Sarah Mendes',
            'role' => 'Experte Fidélisation & Expérience membre',
            'bio' => 'Elle conçoit les parcours d\'onboarding et de rétention pour réduire les résiliations et améliorer le NPS.',
            'initials' => 'SM',
            'accent' => 'orange',
        ],
    ];

    // -------------------------------------------------------------------------
    // Pages
    // -------------------------------------------------------------------------

    public function home(): void
    {
        $this->renderPublic('pages.site.home', [
            'title' => 'RESSOURCES — Le comité d\'experts des dirigeants de clubs de fitness',
            'domains' => self::DOMAINS,
        ], 'home');
    }

    public function experts(): void
    {
        $this->renderPublic('pages.site.experts', [
            'title' => 'Nos experts — RESSOURCES',
            'domains' => self::DOMAINS,
            'lead' => self::CONTACT,
            'experts' => self::EXPERTS,
        ], 'experts');
    }

    public function programs(): void
    {
        $this->renderPublic('pages.site.programs', [
            'title' => 'Programmes — RESSOURCES',
            'categories' => (new CategoryRepository())->topLevel(),
        ], 'programmes');
    }

    /** Page d'une catégorie de programme : vitrine publique + contenu verrouillé. */
    public function programCategory(string $slug): void
    {
        $repo = new CategoryRepository();
        $category = $repo->findBySlug($slug);
        if ($category === null) {
            $this->redirect('/programmes');
        }

        $children = $repo->children($category->id);
        $isMember = Session::isLoggedIn();

        // Vidéo + description + cartes ressources sont publiques ; seul l'accès
        // au contenu d'une ressource (clic) exige d'être authentifié.
        $resRepo = new ResourceRepository();
        $ownResources = $resRepo->listPublishedByCategories([$category->id]);
        $childrenBlocks = [];
        foreach ($children as $child) {
            $childRes = $resRepo->listPublishedByCategories([$child->id]);
            $childrenBlocks[] = [
                'cat' => $child,
                'resources' => array_slice($childRes, 0, 3), // 3 max par sous-catégorie
                'total' => count($childRes),
            ];
        }

        // Fil d'ariane
        $breadcrumb = [];
        $cur = $category->parentId;
        while ($cur !== null) {
            $p = $repo->findById($cur);
            if ($p === null) break;
            array_unshift($breadcrumb, $p);
            $cur = $p->parentId;
        }

        $this->renderPublic('pages.site.program_category', [
            'title' => $category->name . ' — RESSOURCES',
            'category' => $category,
            'children' => $children,
            'children_blocks' => $childrenBlocks,
            'own_resources' => $ownResources,
            'breadcrumb' => $breadcrumb,
            'is_member' => $isMember,
        ], 'programmes');
    }

    /** « Mon espace » (layout public) : infos du membre connecté. */
    public function account(): void
    {
        if (!Session::isLoggedIn()) {
            $this->redirect('/login');
        }
        $user = (new UserRepository())->findById((string) Session::userId());
        if ($user === null) {
            Session::destroy();
            $this->redirect('/login');
        }
        $club = $user->clubId !== null ? (new ClubRepository())->findById($user->clubId) : null;

        $this->renderPublic('pages.site.account', [
            'title' => 'Mon espace — RESSOURCES',
            'user' => $user,
            'club' => $club,
        ], '');
    }

    /** Page d'une ressource (layout public) — réservée aux membres authentifiés. */
    public function programResource(string $id): void
    {
        \App\Middleware\Membership::guard(); // login + club actif requis
        $resource = (new ResourceRepository())->findById($id);
        if ($resource === null || !$resource->isPublished()) {
            $this->redirect('/programmes');
        }
        $repo = new CategoryRepository();
        $category = $resource->categoryId !== null ? $repo->findById($resource->categoryId) : null;

        // Chemin complet des catégories (ancêtres jusqu'à la catégorie de la ressource incluse).
        $breadcrumb = [];
        $cur = $category;
        while ($cur !== null) {
            array_unshift($breadcrumb, $cur);
            $cur = $cur->parentId !== null ? $repo->findById($cur->parentId) : null;
        }

        $this->renderPublic('pages.site.program_resource', [
            'title' => $resource->title . ' — RESSOURCES',
            'resource' => $resource,
            'category' => $category,
            'breadcrumb' => $breadcrumb,
        ], 'programmes');
    }

    public function pricing(): void
    {
        $this->renderPublic('pages.site.pricing', [
            'title' => 'Tarifs — RESSOURCES',
        ], 'prix');
    }

    public function contact(): void
    {
        $this->renderPublic('pages.site.contact', [
            'title' => 'Contact — RESSOURCES',
            'contact' => self::CONTACT,
            'sent' => $this->input('sent') === '1',
            'old' => Session::get('contact_old', []),
        ], 'contact');
        Session::forget('contact_old');
    }

    // -------------------------------------------------------------------------
    // Soumission du formulaire de contact
    // -------------------------------------------------------------------------

    public function submitContact(): void
    {
        Csrf::enforce($this->input('_csrf'));

        // Honeypot anti-spam : champ caché qui doit rester vide.
        if (($this->input('website') ?? '') !== '') {
            $this->redirect('/contact?sent=1');
        }

        $club = $this->input('club');            // Nom du club
        $clubAddress = $this->input('club_address'); // Adresse du club
        $name = $this->input('name');            // Nom du manager
        $firstName = $this->input('first_name'); // Prénom
        $email = $this->input('email');
        $phone = $this->input('phone');
        $message = $this->input('message');

        $errors = [];
        if ($club === null)        { $errors[] = 'Le nom du club est requis.'; }
        if ($clubAddress === null) { $errors[] = 'L\'adresse du club est requise.'; }
        if ($name === null)        { $errors[] = 'Le nom du manager est requis.'; }
        if ($firstName === null)   { $errors[] = 'Le prénom est requis.'; }
        if ($email === null || !filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = 'Un email valide est requis.'; }
        if ($phone === null)       { $errors[] = 'Le téléphone est requis.'; }
        if ($message === null || mb_strlen($message) < 10) { $errors[] = 'Merci de détailler un peu votre demande (10 caractères minimum).'; }

        if ($errors !== []) {
            foreach ($errors as $e) {
                $this->flashError($e);
            }
            Session::set('contact_old', compact('club', 'clubAddress', 'name', 'firstName', 'email', 'phone', 'message'));
            $this->redirect('/contact');
        }

        // Persistance (ne jamais perdre un lead même si l'email SMTP échoue).
        try {
            (new ContactMessageRepository())->create([
                'name' => (string) $name,
                'first_name' => $firstName,
                'email' => (string) $email,
                'phone' => $phone,
                'club' => $club,
                'club_address' => $clubAddress,
                'subject' => null,
                'message' => (string) $message,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            ]);
        } catch (\Throwable $e) {
            error_log('Contact enregistrement échoué : ' . $e->getMessage());
        }

        // Notification email à l'équipe RESSOURCES.
        try {
            $body = '<h2>Nouveau message via le site RESSOURCES</h2>'
                . '<p><strong>Club :</strong> ' . Renderer::escape($club) . '</p>'
                . '<p><strong>Adresse du club :</strong> ' . Renderer::escape($clubAddress) . '</p>'
                . '<p><strong>Manager :</strong> ' . Renderer::escape($firstName) . ' ' . Renderer::escape($name) . '</p>'
                . '<p><strong>Email :</strong> ' . Renderer::escape($email) . '</p>'
                . '<p><strong>Téléphone :</strong> ' . Renderer::escape($phone) . '</p>'
                . '<p><strong>Message :</strong><br>' . nl2br(Renderer::escape($message)) . '</p>';
            (new Mailer())->send(self::CONTACT['email'], self::CONTACT['company'], 'Contact site — ' . $club, $body);
        } catch (\Throwable $e) {
            error_log('Contact email échoué : ' . $e->getMessage());
        }

        $this->flashSuccess('Merci, votre message a bien été envoyé. Nous vous recontactons rapidement.');
        $this->redirect('/contact?sent=1');
    }

    // -------------------------------------------------------------------------
    // Rendu dans le layout public (nav + login header + footer)
    // -------------------------------------------------------------------------

    /** @param array<string,mixed> $data */
    private function renderPublic(string $view, array $data, string $active): void
    {
        $nav = [
            'active' => $active,
            'is_logged_in' => Session::isLoggedIn(),
            'user_name' => (string) Session::get('user_full_name', ''),
            'login_email' => (string) ($this->input('email') ?? ''),
        ];
        $this->render($view, layout: 'layouts.public', data: array_merge($data, ['nav' => $nav]));
    }
}
