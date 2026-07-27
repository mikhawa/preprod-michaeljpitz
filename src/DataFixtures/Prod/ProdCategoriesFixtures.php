<?php

declare(strict_types=1);

namespace App\DataFixtures\Prod;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class ProdCategoriesFixtures extends Fixture implements FixtureGroupInterface
{
    // Constantes de référence utilisables par ProdArticlesFixtures
    public const REF_PHP = 'cat-php';
    public const REF_SYMFONY = 'cat-symfony';
    public const REF_JAVASCRIPT = 'cat-javascript';
    public const REF_DOCTRINE = 'cat-doctrine';
    public const REF_TWIG = 'cat-twig';
    public const REF_STIMULUS = 'cat-stimulus';
    public const REF_TAILWIND = 'cat-tailwind';
    public const REF_MIGRATIONS = 'cat-migrations';

    /** @return string[] */
    public static function getGroups(): array
    {
        return ['prod', 'prod-categories'];
    }

    public function load(ObjectManager $manager): void
    {
        // Catégories racines (level = 0)
        $php = $this->creerCategorie('PHP', 'php', '#7b4f9e', 'Tout sur le langage PHP : bonnes pratiques, nouveautés et astuces.', 0);
        $symfony = $this->creerCategorie('Symfony', 'symfony', '#1a6de0', 'Framework PHP Symfony : composants, bundles et architecture.', 0);
        $javascript = $this->creerCategorie('JavaScript', 'javascript', '#f0db4f', 'JavaScript moderne : ES6+, outils et écosystème front-end.', 0);
        $javascript = $this->creerCategorie('SQL', 'sql', '#06a706', 'Le SQL (Structured Query Language) est un langage informatique standard permettant de communiquer, d\'organiser et de manipuler les données dans des bases de données relationnelles.', 0);

        $manager->persist($php);
        $manager->persist($symfony);
        $manager->persist($javascript);
        $manager->flush();

        // Sous-catégories (level = id du parent)
        $doctrine = $this->creerCategorie('Doctrine ORM', 'doctrine-orm', '#f26522', 'Doctrine ORM : entités, repositories et requêtes DQL.', (int) $php->getId());
        $twig = $this->creerCategorie('Twig', 'twig', '#bacf2e', 'Moteur de templates Twig : filtres, fonctions et héritage.', (int) $symfony->getId());
        $stimulus = $this->creerCategorie('Stimulus.js', 'stimulus-js', '#e04c16', 'Stimulus.js : contrôleurs légers pour enrichir le HTML.', (int) $javascript->getId());
        $tailwind = $this->creerCategorie('Tailwind CSS', 'tailwind-css', '#38bdf8', 'Tailwind CSS : classes utilitaires et design system.', (int) $javascript->getId());

        $manager->persist($doctrine);
        $manager->persist($twig);
        $manager->persist($stimulus);
        $manager->persist($tailwind);
        $manager->flush();

        // Sous-sous-catégorie
        $migrations = $this->creerCategorie('Doctrine Migrations', 'doctrine-migrations', '#c24f1a', 'Gestion des migrations de schéma avec Doctrine Migrations.', (int) $doctrine->getId());

        $manager->persist($migrations);
        $manager->flush();

        // Références pour ProdArticlesFixtures (utilisables dans le même chargement)
        $this->addReference(self::REF_PHP, $php);
        $this->addReference(self::REF_SYMFONY, $symfony);
        $this->addReference(self::REF_JAVASCRIPT, $javascript);
        $this->addReference(self::REF_DOCTRINE, $doctrine);
        $this->addReference(self::REF_TWIG, $twig);
        $this->addReference(self::REF_STIMULUS, $stimulus);
        $this->addReference(self::REF_TAILWIND, $tailwind);
        $this->addReference(self::REF_MIGRATIONS, $migrations);
    }

    private function creerCategorie(string $titre, string $slug, string $couleur, string $description, int $level): Category
    {
        return (new Category())
            ->setTitle($titre)
            ->setSlug($slug)
            ->setColor($couleur)
            ->setDescription($description)
            ->setLevel($level);
    }
}
