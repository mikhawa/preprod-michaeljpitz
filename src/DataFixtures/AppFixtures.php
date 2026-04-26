<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {}

    public function load(ObjectManager $manager): void
    {
        $categories = $this->chargerCategories($manager);
        $admin = $this->chargerAdmin($manager);
        $utilisateurs = $this->chargerUtilisateurs($manager);
        $articles = $this->chargerArticles($manager, $categories, $admin);
        $this->chargerCommentaires($manager, $articles, $utilisateurs);

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
            ->setPassword($this->passwordHasher->hashPassword($admin, 'erapacha25'));

        $manager->persist($admin);
        $manager->flush();

        return $admin;
    }

    /** @return array<int, User> */
    private function chargerUtilisateurs(ObjectManager $manager): array
    {
        $donnees = [
            ['alice.martin@example.com', 'Alice Martin', 1],
            ['bob.dupont@example.com', 'Bob Dupont', 1],
            ['claire.lefebvre@example.com', 'Claire Lefebvre', 1],
            ['david.moreau@example.com', 'David Moreau', 1],
            ['emma.petit@example.com', 'Emma Petit', 1],
            ['francois.bernard@example.com', 'François Bernard', 1],
            ['gaelle.richard@example.com', 'Gaëlle Richard', 1],
            ['hugo.thomas@example.com', 'Hugo Thomas', 1],
            ['isabelle.durand@example.com', 'Isabelle Durand', 0],
            ['julien.leroy@example.com', 'Julien Leroy', 0],
        ];

        $utilisateurs = [];
        foreach ($donnees as [$email, $nom, $statut]) {
            $user = new User();
            $user->setEmail($email)
                ->setUserName($nom)
                ->setStatus($statut)
                ->setPassword($this->passwordHasher->hashPassword($user, 'password123'));

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
