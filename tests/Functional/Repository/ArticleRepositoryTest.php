<?php

declare(strict_types=1);

namespace App\Tests\Functional\Repository;

use App\Repository\ArticleRepository;
use App\Tests\Functional\TestDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ArticleRepositoryTest extends KernelTestCase
{
    use TestDatabaseTrait;

    private ArticleRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->cleanDatabase();
        $this->repository = static::getContainer()->get(ArticleRepository::class);
    }

    public function testFindPublishedQueryBuilderFiltersPublished(): void
    {
        $this->createArticle('Publié', 'article-publie-test', true);
        $this->createArticle('Brouillon', 'article-brouillon-test', false);

        $results = $this->repository->findPublishedQueryBuilder()
            ->getQuery()
            ->getResult();

        self::assertCount(1, $results);
        self::assertSame('Publié', $results[0]->getTitle());
    }

    public function testFindPublishedQueryBuilderFiltersByCategory(): void
    {
        $categoryPhp = $this->createCategory('PHP', 'php-cat');
        $categoryJs = $this->createCategory('JS', 'js-cat');

        $this->createArticle('Article PHP', 'article-php-test', true, $categoryPhp);
        $this->createArticle('Article JS', 'article-js-test-slug', true, $categoryJs);

        $results = $this->repository->findPublishedQueryBuilder($categoryPhp)
            ->getQuery()
            ->getResult();

        self::assertCount(1, $results);
        self::assertSame('Article PHP', $results[0]->getTitle());
    }

    public function testFindPublishedQueryBuilderOrderDesc(): void
    {
        $article1 = $this->createArticle('Premier', 'premier-article-test', true);
        $article2 = $this->createArticle('Deuxieme', 'deuxieme-article-test', true);

        // Forcer des timestamps différents pour garantir l'ordre
        $em = $this->getEntityManager();
        $em->getConnection()->executeStatement(
            'UPDATE article SET published_at = :date WHERE id = :id',
            ['date' => '2025-01-01 10:00:00', 'id' => $article1->getId()],
        );
        $em->getConnection()->executeStatement(
            'UPDATE article SET published_at = :date WHERE id = :id',
            ['date' => '2025-01-02 10:00:00', 'id' => $article2->getId()],
        );

        $em->clear();

        $results = $this->repository->findPublishedQueryBuilder()
            ->getQuery()
            ->getResult();

        self::assertCount(2, $results);
        self::assertSame('Deuxieme', $results[0]->getTitle());
    }

    public function testFindLatestPublishedRespectsLimit(): void
    {
        $this->createArticle('Article 1', 'article-un-test', true);
        $this->createArticle('Article 2', 'article-deux-test', true);
        $this->createArticle('Article 3', 'article-trois-test', true);
        $this->createArticle('Article 4', 'article-quatre-test', true);

        $results = $this->repository->findLatestPublished(2);

        self::assertCount(2, $results);
    }

    public function testFindLatestPublishedDefaultLimit(): void
    {
        for ($i = 1; $i <= 5; ++$i) {
            $this->createArticle("Article $i", "article-num-$i-test", true);
        }

        $results = $this->repository->findLatestPublished();

        self::assertCount(3, $results);
    }

    public function testFindSimilarArticlesByCategory(): void
    {
        $category = $this->createCategory('PHP', 'php-similar');

        $article = $this->createArticle('Article principal', 'article-principal-test', true, $category);
        $this->createArticle('Article similaire', 'article-similaire-test', true, $category);
        $this->createArticle('Article autre', 'article-sans-cat-test', true);

        $results = $this->repository->findSimilarArticles($article);

        self::assertCount(1, $results);
        self::assertSame('Article similaire', $results[0]->getTitle());
    }

    public function testFindSimilarArticlesExcludesCurrentArticle(): void
    {
        $category = $this->createCategory('PHP', 'php-exclude');
        $article = $this->createArticle('Article courant', 'article-courant-test', true, $category);

        $results = $this->repository->findSimilarArticles($article);

        self::assertCount(0, $results);
    }

    public function testFindSimilarArticlesEmptyWhenNoCategory(): void
    {
        $article = $this->createArticle('Sans catégorie', 'sans-categorie-test', true);

        $results = $this->repository->findSimilarArticles($article);

        self::assertCount(0, $results);
    }

    public function testFindLatestPublishedExcludesUnpublished(): void
    {
        $this->createArticle('Publié', 'publie-latest-test', true);
        $this->createArticle('Brouillon', 'brouillon-latest-test', false);

        $results = $this->repository->findLatestPublished();

        self::assertCount(1, $results);
        self::assertSame('Publié', $results[0]->getTitle());
    }
}
