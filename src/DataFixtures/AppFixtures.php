<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Page;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
// Chargement de dépendance automatique
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
        // On injecte directement la variable d'environnement
        #[Autowire(env: 'PASS_ADMIN')]
        private readonly string $mypassword,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $categories = $this->chargerCategories($manager);
        $admin = $this->chargerAdmin($manager);
        $utilisateurs = $this->chargerUtilisateurs($manager);
        $articles = $this->chargerArticles($manager, $categories, $admin);
        $this->chargerCommentaires($manager, $articles, $utilisateurs);
        $this->chargerPages($manager);

        $manager->flush();
    }

    /** @return array<string, Category> */
    private function chargerCategories(ObjectManager $manager): array
    {
        $php = (new Category())
            ->setTitle('PHP')
            ->setSlug('php')
            ->setColor('#7b4f9e')
            ->setDescription('Tout sur le langage PHP : bonnes pratiques, nouveautés et astuces.')
            ->setLevel(0);

        $symfony = (new Category())
            ->setTitle('Symfony')
            ->setSlug('symfony')
            ->setColor('#1a6de0')
            ->setDescription('Framework PHP Symfony : composants, bundles et architecture.')
            ->setLevel(0);

        $javascript = (new Category())
            ->setTitle('JavaScript')
            ->setSlug('javascript')
            ->setColor('#f0db4f')
            ->setDescription('JavaScript moderne : ES6+, outils et écosystème front-end.')
            ->setLevel(0);

        $manager->persist($php);
        $manager->persist($symfony);
        $manager->persist($javascript);
        $manager->flush();

        $doctrine = (new Category())
            ->setTitle('Doctrine ORM')
            ->setSlug('doctrine-orm')
            ->setColor('#f26522')
            ->setDescription('Doctrine ORM : entités, repositories et requêtes DQL.')
            ->setLevel($php->getId());

        $twig = (new Category())
            ->setTitle('Twig')
            ->setSlug('twig')
            ->setColor('#bacf2e')
            ->setDescription('Moteur de templates Twig : filtres, fonctions et héritage.')
            ->setLevel($symfony->getId());

        $stimulus = (new Category())
            ->setTitle('Stimulus.js')
            ->setSlug('stimulus-js')
            ->setColor('#e04c16')
            ->setDescription('Stimulus.js : contrôleurs légers pour enrichir le HTML.')
            ->setLevel($javascript->getId());

        $tailwind = (new Category())
            ->setTitle('Tailwind CSS')
            ->setSlug('tailwind-css')
            ->setColor('#38bdf8')
            ->setDescription('Tailwind CSS : classes utilitaires et design system.')
            ->setLevel($javascript->getId());

        $manager->persist($doctrine);
        $manager->persist($twig);
        $manager->persist($stimulus);
        $manager->persist($tailwind);
        $manager->flush();

        $migrations = (new Category())
            ->setTitle('Doctrine Migrations')
            ->setSlug('doctrine-migrations')
            ->setColor('#c24f1a')
            ->setDescription('Gestion des migrations de schéma avec Doctrine Migrations.')
            ->setLevel($doctrine->getId());

        $manager->persist($migrations);
        $manager->flush();

        return [
            'php' => $php,
            'symfony' => $symfony,
            'javascript' => $javascript,
            'doctrine' => $doctrine,
            'twig' => $twig,
            'stimulus' => $stimulus,
            'tailwind' => $tailwind,
            'migrations' => $migrations,
        ];
    }

    private function chargerAdmin(ObjectManager $manager): User
    {
        $admin = new User();
        $admin->setEmail('michaeljpitz@gmail.com')
            ->setUserName('Michael Pitz')
            ->setRoles(['ROLE_ADMIN'])
            ->setStatus(1)
            ->setBiography('Développeur PHP/Symfony passionné par les bonnes pratiques et l\'architecture logicielle.')
            ->setExternalLink1('https://github.com/mikhawa')
            ->setPassword($this->passwordHasher->hashPassword($admin, $this->mypassword));

        $manager->persist($admin);
        $manager->flush();

        return $admin;
    }

    /** @return array<int, User> */
    private function chargerUtilisateurs(ObjectManager $manager): array
    {
        $donnees = [
            ['michael.pitz@cf2m.be', 'Michael Pitz', 1, 'erapacha25'],
            ['alice.martin@example.com', 'Alice Martin', 1, 'password123'],
            ['bob.dupont@example.com', 'Bob Dupont', 1, 'password123'],
            ['claire.lefebvre@example.com', 'Claire Lefebvre', 1, 'password123'],
            ['david.moreau@example.com', 'David Moreau', 1, 'password123'],
            ['emma.petit@example.com', 'Emma Petit', 1, 'password123'],
            ['francois.bernard@example.com', 'François Bernard', 1, 'password123'],
            ['gaelle.richard@example.com', 'Gaëlle Richard', 1, 'password123'],
            ['hugo.thomas@example.com', 'Hugo Thomas', 1, 'password123'],
            ['isabelle.durand@example.com', 'Isabelle Durand', 0, 'password123'],
            ['julien.leroy@example.com', 'Julien Leroy', 0, 'password123'],
        ];

        $utilisateurs = [];
        foreach ($donnees as [$email, $nom, $statut, $mdp]) {
            $user = new User();
            $user->setEmail($email)
                ->setUserName($nom)
                ->setStatus($statut)
                ->setPassword($this->passwordHasher->hashPassword($user, $mdp));

            $manager->persist($user);
            $utilisateurs[] = $user;
        }

        $manager->flush();

        return $utilisateurs;
    }

    /**
     * @param array<string, Category> $categories
     *
     * @return array<int, Article>
     */
    private function chargerArticles(ObjectManager $manager, array $categories, User $admin): array
    {
        $donnees = [
            ['Introduction à PHP 8.3 : les nouveautés essentielles', 'introduction-php-83-nouveautes-essentielles', 'php', true, 'Découvrez les nouvelles fonctionnalités de PHP 8.3 : types en lecture seule, json_validate() et bien plus encore.'],
            ['Symfony 7.4 : tour d\'horizon des améliorations', 'symfony-74-tour-horizon-ameliorations', 'symfony', true, 'Symfony 7.4 LTS apporte de nombreuses améliorations de performance et de nouvelles fonctionnalités.'],
            ['Doctrine ORM : optimiser vos requêtes avec QueryBuilder', 'doctrine-orm-optimiser-requetes-querybuilder', 'doctrine', true, 'Apprenez à construire des requêtes performantes avec le QueryBuilder de Doctrine ORM.'],
            ['Twig 3 : les filtres et fonctions indispensables', 'twig-3-filtres-fonctions-indispensables', 'twig', true, 'Un guide complet des filtres, fonctions et tags les plus utiles dans Twig 3.'],
            ['Stimulus.js avec Symfony UX : guide pratique', 'stimulus-js-symfony-ux-guide-pratique', 'stimulus', true, 'Intégrez Stimulus.js dans votre projet Symfony via AssetMapper pour une interactivité légère.'],
            ['Tailwind CSS : construire un design system cohérent', 'tailwind-css-construire-design-system-coherent', 'tailwind', true, 'Structurez vos projets Tailwind CSS pour maintenir la cohérence visuelle sur la durée.'],
            ['Les migrations Doctrine : bonnes pratiques', 'migrations-doctrine-bonnes-pratiques', 'migrations', true, 'Gérez l\'évolution de votre schéma de base de données sereinement avec Doctrine Migrations.'],
            ['Sécurité Symfony : protéger son application en production', 'securite-symfony-proteger-application-production', 'symfony', true, 'CSRF, XSS, injection SQL : les menaces et les boucliers natifs de Symfony pour s\'en prémunir.'],
            ['PHP 8 : les attributs remplacent les annotations', 'php-8-attributs-remplacent-annotations', 'php', true, 'Les attributs natifs PHP 8 permettent d\'annoter le code sans dépendances tierces.'],
            ['EasyAdmin 4 avec Symfony : personnalisation avancée', 'easyadmin-4-symfony-personnalisation-avancee', 'symfony', true, 'Personnalisez votre back-office EasyAdmin 4 : CRUD controllers, champs, actions et thème.'],
            ['JavaScript ES2024 : les fonctionnalités à connaître', 'javascript-es2024-fonctionnalites-a-connaitre', 'javascript', true, 'Tour d\'horizon des nouveautés JavaScript ES2024 : groupement, Promise.withResolvers et plus.'],
            ['Doctrine : relations ManyToMany et tables de jonction', 'doctrine-relations-manytomany-tables-jonction', 'doctrine', true, 'Maîtrisez les relations ManyToMany dans Doctrine : configuration, chargement et performance.'],
            ['Symfony AssetMapper : adieu Webpack Encore', 'symfony-assetmapper-adieu-webpack-encore', 'symfony', true, 'AssetMapper simplifie la gestion des assets en tirant parti des imports natifs du navigateur.'],
            ['Tailwind CSS en mode sombre : stratégies et pièges', 'tailwind-css-mode-sombre-strategies-pieges', 'tailwind', true, 'Implémentez un mode sombre fiable avec Tailwind CSS sans casser votre design au fil du temps.'],
            ['Tests fonctionnels Symfony : PHPUnit et WebTestCase', 'tests-fonctionnels-symfony-phpunit-webtestcase', 'symfony', true, 'Écrivez des tests fonctionnels robustes avec PHPUnit et la WebTestCase de Symfony.'],
            ['PHP : programmation orientée objet avancée', 'php-programmation-orientee-objet-avancee', 'php', false, 'Interfaces, traits, classes abstraites et typage strict : approfondissez la POO en PHP.'],
            ['Stimulus.js : communication entre contrôleurs', 'stimulus-js-communication-entre-controleurs', 'stimulus', false, 'Utilisez les outlets et les events Stimulus pour faire communiquer vos contrôleurs.'],
            ['Doctrine : cache de requêtes et performances', 'doctrine-cache-requetes-performances', 'doctrine', false, 'Configurez le cache de second niveau et de résultat pour accélérer vos applications Doctrine.'],
            ['Twig Components dans Symfony UX', 'twig-components-symfony-ux', 'twig', false, 'Créez des composants Twig réutilisables et anonymes pour structurer vos templates.'],
            ['Gestion des migrations en équipe avec Doctrine', 'gestion-migrations-equipe-doctrine', 'migrations', false, 'Évitez les conflits de migrations dans une équipe grâce aux bonnes pratiques Doctrine.'],
        ];

        $articles = [];
        $offset = 0;
        foreach ($donnees as [$titre, $slug, $cat, $publie, $extrait]) {
            $article = new Article();
            $article->setTitle($titre)
                ->setSlug($slug)
                ->setExcerpt($extrait)
                ->setContent($this->genererContenu($titre))
                ->setIsPublished($publie);

            if ($publie) {
                $article->setPublishedAt(new \DateTimeImmutable(sprintf('-%d days', $offset * 3 + 1)));
            }

            $article->addCategory($categories[$cat]);
            $manager->persist($article);
            $articles[] = $article;
            ++$offset;
        }

        $arrayPhp = new Article();
        $arrayPhp->setTitle('Les Array en PHP')
            ->setSlug('les-array-en-php')
            ->setExcerpt('Un tableau en PHP est en fait une carte ordonnée qui associe des valeurs à des clés.')
            ->setFeaturedImage('903ecff8abe47fd4f6e0d8e222a5ee965297926d.jpg')
            ->setIsPublished(true)
            ->setPublishedAt(new \DateTimeImmutable('-1 day'))
            ->setContent('<p>​En <strong>PHP,</strong> un&nbsp;<code><strong>array</strong></code><strong> </strong>(tableau) est une structure de données ordonnée qui associe des <strong>valeurs à des clés</strong>, fonctionnant comme une carte (map), une liste, un dictionnaire ou une collection. Il permet de stocker plusieurs valeurs de types différents (nombres, chaînes, booléens) dans une seule variable. Les tableaux peuvent être numériques (<strong>indexés </strong>par des nombres) ou <strong>associatifs </strong>(indexés par des chaînes).</p>

<p><strong>Ils peuvent être multidimensionnel (imbriqué)</strong></p>

<p><strong>Ils premettent de stocker un grand nombre de valeur dans une seule variable.</strong></p>

<p style="line-height:1.38"><span style="font-size:15px;color:#000000;background-color:transparent">Les<strong> tableaux en PHP</strong> sont omniprésents : des entrées utilisateur aux résultats de base de données, en passant par les paramètres et les configurations. Ils sont incroyablement flexibles, mais la plupart d&apos;entre nous n&apos;en connaissent qu&apos;une infime partie.</span></p>

<h2 style="line-height: 1.38"><span style="font-size:15px;color:#000000;background-color:transparent">Déclaration d&apos;un tableau</span></h2>

<p>[php]# la plus ancienne méthode, mais<br>
# est toujours fonctionnelle!<br>
$tableau1 = array();<br>
<br>
# ou la plus fréquente<br>
$tableau2 = [];<br>
<br>
# Débogage, donne 2 tableaux vides<br>
var_dump($tableau1, $tableau2);[/php]</p>

<h2>Tableau indexé</h2>

<p>Des numériques en partant de 0 servent de clé pour pouvoir afficher la valeur en question.</p>

<p>[php]# Ce type de tableau n&apos;est déclaré qu&apos;avec des valeurs<br>
$tab_index = [<br>
    &apos;un&apos;, <br>
    &apos;deux&apos;, <br>
    &apos;trois&apos;,<br>
];<br>
<br>
<br>
# On veut afficher la &apos;un&apos;<br>
echo $tab_index[0];<br>
<br>
# retour à la ligne (\n)<br>
echo PHP_EOL;<br>
<br>
<br>
# débogage<br>
var_dump($tab_index);[/php]</p>

<p><br>
</p>

<h2>Tableau associatif&nbsp;</h2>

<p>Des chaînes de caractères servent de clé pour pouvoir afficher la valeur en question.</p>

<p><br>
</p>

<p>[php]# Ce type de tableau est déclaré qu&apos;avec des clés =&gt; valeurs<br>
$tab_assoc = [<br>
    &apos;one&apos;=&gt;&apos;un&apos;,<br>
     &apos;two&apos;=&gt;&apos;deux&apos;, <br>
    &apos;three&apos;=&gt;&apos;trois&apos;<br>
 ];<br>
<br>
# On veut afficher la &apos;un&apos;<br>
echo $tab_assoc[&apos;one&apos;];<br>
<br>
# retour à la ligne<br>
echo PHP_EOL;<br>
<br>
# débogage<br>
var_dump($tab_assoc);[/php]</p>

<h2>Tableau Multidimensionnel</h2>

<p>Un tableau peut avoir des sous tableaux, et donc contenir énormément d&apos;informations !</p>

<p>[php]# Liste d&apos;élèves ayant participer à 3 contrôles<br>
<br>
$stagiaires = [<br>
    &apos;classe&apos; =&gt; &apos;2CB&apos;,<br>
    &apos;date&apos; =&gt; &apos;2026-04-18&apos;,<br>
    [<br>
        &apos;nom&apos; =&gt; &apos;Ben Adj&apos;,<br>
        &apos;prenom&apos; =&gt; &apos;Meidhy&apos;,<br>
        &apos;matière&apos; =&gt; [<br>
            &apos;Français&apos; =&gt; &apos;18.5/20&apos;,<br>
            &apos;Géographie&apos; =&gt; &apos;16/20&apos;,<br>
            &apos;Mathématique&apos; =&gt; &apos;14.5/20&apos;,<br>
        ],<br>
    ],<br>
     [<br>
        &apos;nom&apos; =&gt; &apos;Pitz&apos;,<br>
        &apos;prenom&apos; =&gt; &apos;Michaël&apos;,<br>
        &apos;matière&apos; =&gt; [<br>
            &apos;Français&apos; =&gt; &apos;17/20&apos;,<br>
            &apos;Géographie&apos; =&gt; &apos;18/20&apos;,<br>
            &apos;Mathématique&apos; =&gt; &apos;17/20&apos;,<br>
        ],<br>
    ],<br>
];</p>

<p><br>
</p>

<p># pour récupérer la cote en Français de Pitz Michaël:</p>

<p>echo $stagiaires[1][&apos;matière&apos;][&apos;Français&apos;];</p>

<p><br>
</p>

<p># retour à la ligne</p>

<p>echo PHP_EOL;</p>

<p><br>
</p>

<p># Débogage avec print_r (plus lisible)</p>

<p>print_r($stagiaires);[/php]</p>

<p><br>
</p>
');
        $arrayPhp->addCategory($categories['php']);
        $manager->persist($arrayPhp);
        $articles[] = $arrayPhp;

        $manager->flush();

        return $articles;
    }

    /**
     * @param array<int, Article> $articles
     * @param array<int, User>    $utilisateurs
     */
    private function chargerCommentaires(ObjectManager $manager, array $articles, array $utilisateurs): void
    {
        $donnees = [
            ['Excellent article, très bien expliqué ! J\'ai enfin compris ce concept.', true, 0, 0],
            ['Merci pour ce contenu de qualité, j\'attends la suite avec impatience.', true, 1, 1],
            ['Quelques points mériteraient d\'être approfondis, mais la base est là.', true, 2, 2],
            ['J\'ai appliqué cette approche sur mon projet et ça fonctionne parfaitement.', true, 3, 3],
            ['Très clair et concis, exactement ce que je cherchais pour débuter.', true, 4, 0],
            ['La section sur les performances est particulièrement intéressante.', true, 5, 1],
            ['Avez-vous un exemple de projet GitHub pour accompagner cet article ?', true, 6, 2],
            ['Je recommande cet article à tous mes collègues développeurs.', true, 7, 3],
            ['Ce point mériterait une correction, l\'exemple du code ligne 3 est incorrect.', false, 8, 4],
            ['Pourquoi ne pas utiliser plutôt l\'approche X dans ce cas précis ?', false, 9, 5],
            ['Lien spam www.spam-example.tld', false, 10, 6],
            ['Article intéressant mais j\'ai du mal avec la partie sur les migrations.', false, 11, 7],
        ];

        foreach ($donnees as [$contenu, $approuve, $indexArticle, $indexUser]) {
            $commentaire = new Comment();
            $commentaire->setContent($contenu)
                ->setIsApproved($approuve)
                ->setUser($utilisateurs[$indexUser])
                ->setArticle($articles[$indexArticle]);

            $manager->persist($commentaire);
        }
    }

    private function chargerPages(ObjectManager $manager): void
    {
        $rgpd = new Page();
        $rgpd->setTitle('Politique de confidentialité (RGPD)')
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
        $manager->persist($rgpd);

        $contact = new Page();
        $contact->setTitle('Contact')
            ->setSlug('contact')
            ->setContent(
                '<p>Vous souhaitez me contacter ? Remplissez le formulaire ci-dessous et je vous répondrai dans les plus brefs délais.</p>'
            );
        $manager->persist($contact);

        $cv = new Page();
        $cv->setTitle('Curriculum Vitae')
            ->setSlug('cv')
            ->setContent(<<<HTML
                <h1>Michaël J. Pitz</h1>

                <p><strong>Développeur Web Senior &amp; Formateur Professionnel</strong><br>
                📍 Région de Bruxelles-Capitale, Belgique | ✉️ michaeljpitz@gmail.com | 📞 +32477380302<br>
                🔗 <a target="_blank" href="https://www.linkedin.com/in/michaeljpitz/">LinkedIn</a> /
                <a target="_blank" href="https://github.com/mikhawa">GitHub</a> |
                🌐 <a href="https://preprod.michaeljpitz.com/">Accueil - Portfolio Développeur PHP/Symfony</a></p>

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

                <p><strong>Formateur Professionnel en Développement Web</strong><br>
                <em>Centre de formation CF2M, Bruxelles | 2019 – Présent</em></p>

                <ul>
                    <li><p>Conception et animation de programmes de formation technique pour les futurs développeurs.</p></li>
                    <li><p>Création de projets pédagogiques complets, dont un projet de jeu multi-étapes destiné
                    à l'enseignement de la Programmation Orientée Objet (POO) sous PHP 8.x.</p></li>
                    <li><p>Accompagnement technique des apprenants sur la structuration de code et les bonnes pratiques.</p></li>
                </ul>

                <p><strong>Développeur Web Senior &amp; Gestionnaire d'Infrastructure</strong><br>
                <em>Indépendant | 2003 – Présent</em></p>

                <ul>
                    <li><p>Conception et développement d'API, de plateformes éducatives et de sites e-commerce complexes.</p></li>
                    <li><p>Pilotage de la transition et de la migration de projets web vers Symfony 7.4 LTS, incluant
                    la résolution de problématiques liées à EasyAdmin, AssetMapper et la transformation de fichiers
                    <code>composer.json</code>.</p></li>
                    <li><p>Développement sur mesure utilisant du PHP "fait main" et le moteur de template Twig
                    pour des besoins spécifiques nécessitant une architecture allégée.</p></li>
                    <li><p>Administration et gestion d'infrastructures serveurs : déploiement sous AlmaLinux 8 et 9,
                    conteneurisation avec Docker, et administration via Plesk, CyberPanel, Webmin et Termux.</p></li>
                    <li><p>Gestion des migrations de serveurs, des installations SSL et de la configuration des services SMTP.</p></li>
                    <li><p>Édition, gestion de l'architecture et optimisation SEO (traductions incluses) d'un blog
                    technique personnel dédié au développement.</p></li>
                </ul>

                <h2>Compétences Techniques</h2>

                <p><strong>Développement &amp; Langages</strong></p>
                <ul>
                    <li><p><strong>Expertise :</strong> PHP (8.x), POO, Twig</p></li>
                    <li><p><strong>Frameworks :</strong> Symfony (spécialisation 7.x LTS), Laravel</p></li>
                    <li><p><strong>Outils &amp; ORM :</strong> Doctrine, Composer, AssetMapper</p></li>
                    <li><p><strong>Exploration &amp; Divers :</strong> WebAssembly, Rust, développement d'API</p></li>
                </ul>

                <p><strong>Systèmes &amp; Réseaux</strong></p>
                <ul>
                    <li><p><strong>OS Serveurs :</strong> AlmaLinux 8/9, environnements Linux en ligne de commande</p></li>
                    <li><p><strong>DevOps &amp; Hébergement :</strong> Docker, Plesk, CyberPanel, Webmin, Termux</p></li>
                    <li><p><strong>Administration :</strong> Migrations, gestion des certificats SSL, configurations SMTP réseau</p></li>
                </ul>

                <p><strong>Innovation &amp; Veille</strong></p>
                <ul>
                    <li><p>Intégration et analyse de modèles LLM (Claude, Gemini, Mistral).</p></li>
                </ul>

                <h2>Projets Récents</h2>

                <ul>
                    <li><p><strong>Techblog (techblog.michaeljpitz.com) :</strong> Création et maintenance d'un blog
                    technique personnel, gestion de la structure du contenu et des problématiques de traduction SEO.</p></li>
                    <li><p><strong>Projet Pédagogique POO :</strong> Développement d'un cursus basé sur la création
                    d'un jeu vidéo en PHP pour vulgariser les concepts de la programmation orientée objet.</p></li>
                </ul>

                <h2>Centres d'Intérêt</h2>

                <ul>
                    <li><p><strong>Sciences exactes :</strong> Passion prononcée pour la chimie organique, la physique
                    quantique et la microbiologie (structures moléculaires, fonctions d'onde, identification de
                    micro-organismes).</p></li>
                    <li><p><strong>Géopolitique &amp; Technologies :</strong> Analyse des systèmes de défense
                    technologiques et compréhension des classements de puissance économique mondiale.</p></li>
                </ul>
                HTML);
        $manager->persist($cv);

        $manager->flush();
    }

    private function genererContenu(string $titre): string
    {
        return '<h2>'.$titre.'</h2>'
            .'<p>Cet article explore en détail les concepts fondamentaux et avancés liés à ce sujet. Que vous soyez débutant ou développeur confirmé, vous trouverez ici des informations pratiques et des exemples concrets.</p>'
            .'<h3>Pourquoi ce sujet est important</h3>'
            .'<p>Dans le développement web moderne, maîtriser ces concepts vous permettra d\'écrire un code plus maintenable, plus performant et plus sécurisé. Les équipes qui adoptent ces pratiques constatent une réduction significative du temps de débogage.</p>'
            .'<h3>Mise en pratique</h3>'
            .'<p>Voici comment appliquer ces connaissances dans un projet Symfony réel. Commencez par configurer votre environnement de développement correctement, puis suivez les étapes décrites ci-dessous.</p>'
            .'<pre><code>// Exemple de code commenté&#10;// Adaptez ce code à votre contexte</code></pre>'
            .'<h3>Points clés à retenir</h3>'
            .'<ul>'
            .'<li>Toujours utiliser le typage strict en PHP 8+</li>'
            .'<li>Privilégier les attributs PHP aux annotations pour la configuration</li>'
            .'<li>Tester régulièrement avec PHPUnit pour garantir la stabilité</li>'
            .'</ul>'
            .'<p>N\'hésitez pas à laisser un commentaire si vous avez des questions ou des suggestions d\'amélioration.</p>';
    }
}
