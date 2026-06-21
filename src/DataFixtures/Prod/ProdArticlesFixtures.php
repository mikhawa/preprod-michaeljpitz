<?php

declare(strict_types=1);

namespace App\DataFixtures\Prod;

use App\Entity\Article;
use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ProdArticlesFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    /** @return string[] */
    public static function getGroups(): array
    {
        return ['prod', 'prod-articles'];
    }

    /** @return string[] */
    public function getDependencies(): array
    {
        return [ProdCategoriesFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        // Articles en brouillon (isPublished = false) — contenu à compléter via EasyAdmin
        $brouillons = [
            ['Introduction à PHP 8.3 : les nouveautés essentielles', 'introduction-php-83-nouveautes-essentielles', ProdCategoriesFixtures::REF_PHP, 'Découvrez les nouvelles fonctionnalités de PHP 8.3 : types en lecture seule, json_validate() et bien plus encore.'],
            ['Symfony 7.4 : tour d\'horizon des améliorations', 'symfony-74-tour-horizon-ameliorations', ProdCategoriesFixtures::REF_SYMFONY, 'Symfony 7.4 LTS apporte de nombreuses améliorations de performance et de nouvelles fonctionnalités.'],
            ['Doctrine ORM : optimiser vos requêtes avec QueryBuilder', 'doctrine-orm-optimiser-requetes-querybuilder', ProdCategoriesFixtures::REF_DOCTRINE, 'Apprenez à construire des requêtes performantes avec le QueryBuilder de Doctrine ORM.'],
            ['Twig 3 : les filtres et fonctions indispensables', 'twig-3-filtres-fonctions-indispensables', ProdCategoriesFixtures::REF_TWIG, 'Un guide complet des filtres, fonctions et tags les plus utiles dans Twig 3.'],
            ['Stimulus.js avec Symfony UX : guide pratique', 'stimulus-js-symfony-ux-guide-pratique', ProdCategoriesFixtures::REF_STIMULUS, 'Intégrez Stimulus.js dans votre projet Symfony via AssetMapper pour une interactivité légère.'],
            ['Tailwind CSS : construire un design system cohérent', 'tailwind-css-construire-design-system-coherent', ProdCategoriesFixtures::REF_TAILWIND, 'Structurez vos projets Tailwind CSS pour maintenir la cohérence visuelle sur la durée.'],
            ['Les migrations Doctrine : bonnes pratiques', 'migrations-doctrine-bonnes-pratiques', ProdCategoriesFixtures::REF_MIGRATIONS, 'Gérez l\'évolution de votre schéma de base de données sereinement avec Doctrine Migrations.'],
            ['Sécurité Symfony : protéger son application en production', 'securite-symfony-proteger-application-production', ProdCategoriesFixtures::REF_SYMFONY, 'CSRF, XSS, injection SQL : les menaces et les boucliers natifs de Symfony pour s\'en prémunir.'],
            ['PHP 8 : les attributs remplacent les annotations', 'php-8-attributs-remplacent-annotations', ProdCategoriesFixtures::REF_PHP, 'Les attributs natifs PHP 8 permettent d\'annoter le code sans dépendances tierces.'],
            ['EasyAdmin 4 avec Symfony : personnalisation avancée', 'easyadmin-4-symfony-personnalisation-avancee', ProdCategoriesFixtures::REF_SYMFONY, 'Personnalisez votre back-office EasyAdmin 4 : CRUD controllers, champs, actions et thème.'],
            ['JavaScript ES2024 : les fonctionnalités à connaître', 'javascript-es2024-fonctionnalites-a-connaitre', ProdCategoriesFixtures::REF_JAVASCRIPT, 'Tour d\'horizon des nouveautés JavaScript ES2024 : groupement, Promise.withResolvers et plus.'],
            ['Doctrine : relations ManyToMany et tables de jonction', 'doctrine-relations-manytomany-tables-jonction', ProdCategoriesFixtures::REF_DOCTRINE, 'Maîtrisez les relations ManyToMany dans Doctrine : configuration, chargement et performance.'],
            ['Symfony AssetMapper : adieu Webpack Encore', 'symfony-assetmapper-adieu-webpack-encore', ProdCategoriesFixtures::REF_SYMFONY, 'AssetMapper simplifie la gestion des assets en tirant parti des imports natifs du navigateur.'],
            ['Tailwind CSS en mode sombre : stratégies et pièges', 'tailwind-css-mode-sombre-strategies-pieges', ProdCategoriesFixtures::REF_TAILWIND, 'Implémentez un mode sombre fiable avec Tailwind CSS sans casser votre design au fil du temps.'],
            ['Tests fonctionnels Symfony : PHPUnit et WebTestCase', 'tests-fonctionnels-symfony-phpunit-webtestcase', ProdCategoriesFixtures::REF_SYMFONY, 'Écrivez des tests fonctionnels robustes avec PHPUnit et la WebTestCase de Symfony.'],
            ['PHP : programmation orientée objet avancée', 'php-programmation-orientee-objet-avancee', ProdCategoriesFixtures::REF_PHP, 'Interfaces, traits, classes abstraites et typage strict : approfondissez la POO en PHP.'],
            ['Stimulus.js : communication entre contrôleurs', 'stimulus-js-communication-entre-controleurs', ProdCategoriesFixtures::REF_STIMULUS, 'Utilisez les outlets et les events Stimulus pour faire communiquer vos contrôleurs.'],
            ['Doctrine : cache de requêtes et performances', 'doctrine-cache-requetes-performances', ProdCategoriesFixtures::REF_DOCTRINE, 'Configurez le cache de second niveau et de résultat pour accélérer vos applications Doctrine.'],
            ['Twig Components dans Symfony UX', 'twig-components-symfony-ux', ProdCategoriesFixtures::REF_TWIG, 'Créez des composants Twig réutilisables et anonymes pour structurer vos templates.'],
            ['Gestion des migrations en équipe avec Doctrine', 'gestion-migrations-equipe-doctrine', ProdCategoriesFixtures::REF_MIGRATIONS, 'Évitez les conflits de migrations dans une équipe grâce aux bonnes pratiques Doctrine.'],
        ];

        foreach ($brouillons as [$titre, $slug, $refCategorie, $extrait]) {
            /** @var Category $categorie */
            $categorie = $this->getReference($refCategorie, Category::class);

            $article = (new Article())
                ->setTitle($titre)
                ->setSlug($slug)
                ->setExcerpt($extrait)
                ->setContent('<p>Contenu à rédiger.</p>')
                ->setIsPublished(false)
                ->addCategory($categorie);

            $manager->persist($article);
        }

        // Article publié avec contenu réel
        /** @var Category $php */
        $php = $this->getReference(ProdCategoriesFixtures::REF_PHP, Category::class);

        $arrayPhp = (new Article())
            ->setTitle('Les Array en PHP')
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
')
            ->addCategory($php);

        $manager->persist($arrayPhp);
        $manager->flush();
    }
}
