<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Insertion des pages CV et RGPD avec leur contenu initial.
 */
final class Version20260206131000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Insertion des pages CV et RGPD avec leur contenu initial';
    }

    public function up(Schema $schema): void
    {
        $cvContent = '<h2>Compétences</h2>'
            .'<ul>'
            .'<li>PHP 8 / Symfony 7</li>'
            .'<li>Doctrine ORM / MySQL / MariaDB</li>'
            .'<li>HTML5 / CSS3 / Tailwind CSS</li>'
            .'<li>JavaScript / Stimulus</li>'
            .'<li>Docker / Git / CI-CD</li>'
            .'<li>API REST / Tests unitaires</li>'
            .'</ul>'
            .'<h2>Expériences</h2>'
            .'<h3>Développeur PHP/Symfony</h3>'
            .'<p>Entreprise XYZ - 2023-2026</p>'
            .'<p>Développement d\'applications web, API REST, intégration continue.</p>'
            .'<h3>Développeur Web Junior</h3>'
            .'<p>Agence ABC - 2021-2023</p>'
            .'<p>Création de sites WordPress et Symfony, maintenance applicative.</p>'
            .'<h2>Formations</h2>'
            .'<h3>Licence Informatique</h3>'
            .'<p>Université - 2020</p>'
            .'<h3>BTS SIO option SLAM</h3>'
            .'<p>Lycée - 2018</p>'
            .'<h3>Certifications Symfony</h3>'
            .'<p>SymfonyCasts - 2022</p>';

        $rgpdContent = '<h2>Collecte des données</h2>'
            .'<p>Ce site collecte des données personnelles uniquement dans le cadre de son fonctionnement normal :</p>'
            .'<ul>'
            .'<li>Formulaire de contact : nom, adresse e-mail, message</li>'
            .'<li>Inscription : nom d\'utilisateur, adresse e-mail, mot de passe (hashé)</li>'
            .'<li>Commentaires : contenu du commentaire associé à votre compte</li>'
            .'</ul>'
            .'<h2>Utilisation des données</h2>'
            .'<p>Les données collectées sont utilisées exclusivement pour :</p>'
            .'<ul>'
            .'<li>Répondre à vos demandes de contact</li>'
            .'<li>Gérer votre compte utilisateur</li>'
            .'<li>Afficher vos commentaires sur les articles</li>'
            .'</ul>'
            .'<p>Aucune donnée n\'est transmise à des tiers ni utilisée à des fins commerciales.</p>'
            .'<h2>Cookies</h2>'
            .'<p>Ce site utilise uniquement des cookies techniques nécessaires à son fonctionnement :</p>'
            .'<ul>'
            .'<li>Cookie de session (authentification)</li>'
            .'<li>Cookie de préférence de thème (clair/sombre)</li>'
            .'</ul>'
            .'<p>Aucun cookie de pistage ou publicitaire n\'est utilisé.</p>'
            .'<h2>Vos droits</h2>'
            .'<p>Conformément au Règlement Général sur la Protection des Données (RGPD), vous disposez des droits suivants :</p>'
            .'<ul>'
            .'<li>Droit d\'accès à vos données personnelles</li>'
            .'<li>Droit de rectification de vos données</li>'
            .'<li>Droit à l\'effacement de vos données</li>'
            .'<li>Droit à la portabilité de vos données</li>'
            .'<li>Droit d\'opposition au traitement de vos données</li>'
            .'</ul>'
            .'<h2>Contact</h2>'
            .'<p>Pour exercer vos droits ou pour toute question relative à la protection de vos données, vous pouvez nous contacter via la page de contact.</p>';

        $now = (new \DateTimeImmutable())->format('Y-m-d H:i:s');

        $this->addSql('INSERT INTO page (title, slug, content, created_at) VALUES (:title, :slug, :content, :created_at)', [
            'title' => 'Curriculum Vitae',
            'slug' => 'cv',
            'content' => $cvContent,
            'created_at' => $now,
        ]);

        $this->addSql('INSERT INTO page (title, slug, content, created_at) VALUES (:title, :slug, :content, :created_at)', [
            'title' => 'Politique de confidentialité (RGPD)',
            'slug' => 'rgpd',
            'content' => $rgpdContent,
            'created_at' => $now,
        ]);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM page WHERE slug IN (:cv, :rgpd)', [
            'cv' => 'cv',
            'rgpd' => 'rgpd',
        ]);
    }
}
