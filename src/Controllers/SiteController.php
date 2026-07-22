<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Csrf;
use App\Helpers\Renderer;
use App\Repositories\ContactMessageRepository;
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
        ['Hospitality', 'Qualité de service et expérience membre au quotidien.'],
        ['Ressources humaines', 'Recrutement, intégration et management des équipes terrain.'],
        ['Process de vente', 'Structurer et fiabiliser le parcours commercial.'],
        ['Vente additionnelle', 'Développer le panier moyen et les revenus complémentaires.'],
        ['Communication', 'Communication locale et présence sur les réseaux.'],
        ['Marketing', 'Acquisition, positionnement et campagnes saisonnières.'],
        ['Services sportifs', 'Offre de cours, planning et différenciation sportive.'],
        ['Piloter le club / KPI', 'Indicateurs clés, tableaux de bord et rentabilité.'],
        ['Anticiper demain', 'Tendances marché, veille et opportunités de croissance.'],
        ['Création', 'Ouverture de club, concept et business plan.'],
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

        $name = $this->input('name');
        $email = $this->input('email');
        $message = $this->input('message');
        $phone = $this->input('phone');
        $club = $this->input('club');
        $subject = $this->input('subject') ?? 'Demande via le site';

        $errors = [];
        if ($name === null) {
            $errors[] = 'Votre nom est requis.';
        }
        if ($email === null || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Un email valide est requis.';
        }
        if ($message === null || mb_strlen($message) < 10) {
            $errors[] = 'Merci de détailler un peu votre demande (10 caractères minimum).';
        }

        if ($errors !== []) {
            foreach ($errors as $e) {
                $this->flashError($e);
            }
            Session::set('contact_old', compact('name', 'email', 'phone', 'club', 'subject', 'message'));
            $this->redirect('/contact');
        }

        // Persistance (ne jamais perdre un lead même si l'email SMTP échoue).
        try {
            (new ContactMessageRepository())->create([
                'name' => (string) $name,
                'email' => (string) $email,
                'phone' => $phone,
                'club' => $club,
                'subject' => $subject,
                'message' => (string) $message,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            ]);
        } catch (\Throwable $e) {
            error_log('Contact enregistrement échoué : ' . $e->getMessage());
        }

        // Notification email à l'équipe RESSOURCES.
        try {
            $body = '<h2>Nouveau message via le site RESSOURCES</h2>'
                . '<p><strong>Nom :</strong> ' . Renderer::escape($name) . '</p>'
                . '<p><strong>Email :</strong> ' . Renderer::escape($email) . '</p>'
                . '<p><strong>Téléphone :</strong> ' . Renderer::escape($phone ?? '—') . '</p>'
                . '<p><strong>Club :</strong> ' . Renderer::escape($club ?? '—') . '</p>'
                . '<p><strong>Sujet :</strong> ' . Renderer::escape($subject) . '</p>'
                . '<p><strong>Message :</strong><br>' . nl2br(Renderer::escape($message)) . '</p>';
            (new Mailer())->send(self::CONTACT['email'], self::CONTACT['company'], 'Contact site — ' . $subject, $body);
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
