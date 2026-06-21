<?php

declare(strict_types=1);

namespace App\DataFixtures\Prod;

use App\Entity\Page;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class ProdPagesFixtures extends Fixture implements FixtureGroupInterface
{
    /** @return string[] */
    public static function getGroups(): array
    {
        return ['prod', 'prod-pages'];
    }

    public function load(ObjectManager $manager): void
    {
        $manager->persist($this->creerPageRgpd());
        $manager->persist($this->creerPageContact());
        $manager->persist($this->creerPageCv());
        $manager->flush();
    }

    private function creerPageRgpd(): Page
    {
        return (new Page())
            ->setTitle('Politique de confidentialité (RGPD)')
            ->setSlug('rgpd')
            ->setContent(
                '<h2>Collecte des données</h2>'
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
                .'<p>Pour exercer vos droits ou pour toute question relative à la protection de vos données, vous pouvez nous contacter via la page <a href="/contact">Contact</a>.</p>'
            );
    }

    private function creerPageContact(): Page
    {
        return (new Page())
            ->setTitle('Contact')
            ->setSlug('contact')
            ->setContent(
                '<p>Vous souhaitez me contacter ? Remplissez le formulaire ci-dessous et je vous répondrai dans les plus brefs délais.</p>'
            );
    }

    private function creerPageCv(): Page
    {
        return (new Page())
            ->setTitle('Curriculum Vitae')
            ->setSlug('cv')
            ->setContent(<<<HTML
                <h1>Michaël J. Pitz</h1>
                <p>
                    <strong>Développeur Web Senior &amp; Formateur Professionnel</strong><br>
                    📍 Région de Bruxelles-Capitale, Belgique | ✉️ michaeljpitz@gmail.com | 📞 +32477380302<br>
                    🔗 <a target="_blank" href="https://www.linkedin.com/in/michaeljpitz/">LinkedIn</a> /
                    <a target="_blank" href="https://github.com/mikhawa">GitHub</a> |
                    🌐 <a href="https://preprod.michaeljpitz.com/">Accueil - Portfolio Développeur PHP/Symfony</a>
                </p>

                <h2>Profil</h2>
                <p>Professionnel de l'informatique avec plus de 20 ans d'expérience dans le développement web et
                la gestion d'infrastructures. Expert dans l'écosystème PHP (notamment Symfony et Laravel), avec
                une passion pour le partage de connaissances en tant que formateur. Veille technologique très
                active axée sur les technologies émergentes (agents IA, WebAssembly, Rust) et forte curiosité
                intellectuelle pour les sciences pures (physique quantique, chimie) et la géopolitique.</p>

                <h2>Compétences</h2>
                <ul>
                    <li>PHP 8 / Symfony 7</li>
                    <li>Doctrine ORM / MySQL / MariaDB</li>
                    <li>HTML5 / CSS3 / Tailwind CSS</li>
                    <li>JavaScript / Stimulus</li>
                    <li>Docker / Git / CI-CD</li>
                    <li>API REST / Tests unitaires</li>
                </ul>

                <h2>Expériences Professionnelles</h2>

                <h3>Formateur Professionnel en Développement Web</h3>
                <p><em>Centre de formation CF2M, Bruxelles | 2019 – Présent</em></p>
                <ul>
                    <li>Conception et animation de programmes de formation technique pour les futurs développeurs.</li>
                    <li>Création de projets pédagogiques complets, dont un projet de jeu multi-étapes destiné
                        à l'enseignement de la Programmation Orientée Objet (POO) sous PHP 8.x.</li>
                    <li>Accompagnement technique des apprenants sur la structuration de code et les bonnes pratiques.</li>
                </ul>

                <h3>Développeur Web Senior &amp; Gestionnaire d'Infrastructure</h3>
                <p><em>Indépendant | 2003 – Présent</em></p>
                <ul>
                    <li>Conception et développement d'API, de plateformes éducatives et de sites e-commerce complexes.</li>
                    <li>Pilotage de la transition et de la migration de projets web vers Symfony 7.4 LTS, incluant
                        la résolution de problématiques liées à EasyAdmin, AssetMapper et la transformation de fichiers
                        <code>composer.json</code>.</li>
                    <li>Développement sur mesure utilisant du PHP "fait main" et le moteur de template Twig
                        pour des besoins spécifiques nécessitant une architecture allégée.</li>
                    <li>Administration et gestion d'infrastructures serveurs : déploiement sous AlmaLinux 8 et 9,
                        conteneurisation avec Docker, et administration via Plesk, CyberPanel, Webmin et Termux.</li>
                    <li>Gestion des migrations de serveurs, des installations SSL et de la configuration des services SMTP.</li>
                    <li>Édition, gestion de l'architecture et optimisation SEO (traductions incluses) d'un blog
                        technique personnel dédié au développement.</li>
                </ul>

                <h2>Compétences Techniques</h2>

                <h3>Développement &amp; Langages</h3>
                <ul>
                    <li><strong>Expertise :</strong> PHP (8.x), POO, Twig</li>
                    <li><strong>Frameworks :</strong> Symfony (spécialisation 7.x LTS), Laravel</li>
                    <li><strong>Outils &amp; ORM :</strong> Doctrine, Composer, AssetMapper</li>
                    <li><strong>Exploration &amp; Divers :</strong> WebAssembly, Rust, développement d'API</li>
                </ul>

                <h3>Systèmes &amp; Réseaux</h3>
                <ul>
                    <li><strong>OS Serveurs :</strong> AlmaLinux 8/9, environnements Linux en ligne de commande</li>
                    <li><strong>DevOps &amp; Hébergement :</strong> Docker, Plesk, CyberPanel, Webmin, Termux</li>
                    <li><strong>Administration :</strong> Migrations, gestion des certificats SSL, configurations SMTP réseau</li>
                </ul>

                <h3>Innovation &amp; Veille</h3>
                <ul>
                    <li>Intégration et analyse de modèles LLM (Claude, Gemini, Mistral).</li>
                </ul>

                <h2>Projets Récents</h2>
                <ul>
                    <li><strong>Techblog (techblog.michaeljpitz.com) :</strong> Création et maintenance d'un blog
                        technique personnel, gestion de la structure du contenu et des problématiques de traduction SEO.</li>
                    <li><strong>Projet Pédagogique POO :</strong> Développement d'un cursus basé sur la création
                        d'un jeu vidéo en PHP pour vulgariser les concepts de la programmation orientée objet.</li>
                </ul>

                <h2>Centres d'Intérêt</h2>
                <ul>
                    <li><strong>Sciences exactes :</strong> Passion prononcée pour la chimie organique, la physique
                        quantique et la microbiologie (structures moléculaires, fonctions d'onde, identification
                        de micro-organismes).</li>
                    <li><strong>Géopolitique &amp; Technologies :</strong> Analyse des systèmes de défense
                        technologiques et compréhension des classements de puissance économique mondiale.</li>
                </ul>
                HTML);
    }
}