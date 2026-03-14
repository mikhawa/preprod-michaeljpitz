<?php

declare(strict_types=1);

namespace App\Tests\Functional\Repository;

use App\Repository\CommentRepository;
use App\Tests\Functional\TestDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CommentRepositoryTest extends KernelTestCase
{
    use TestDatabaseTrait;

    private CommentRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->cleanDatabase();
        $this->repository = static::getContainer()->get(CommentRepository::class);
    }

    public function testFindByArticleReturnsOnlyApproved(): void
    {
        $user = $this->createActiveUser();
        $article = $this->createArticle();

        $this->createComment($user, $article, 'Commentaire approuvé suffisant.', true);
        $this->createComment($user, $article, 'Commentaire non approuvé assez long.', false);

        $results = $this->repository->findByArticle($article);

        self::assertCount(1, $results);
        self::assertTrue($results[0]->isApproved());
    }

    public function testFindByArticleOrderDesc(): void
    {
        $user = $this->createActiveUser();
        $article = $this->createArticle();

        $comment1 = $this->createComment($user, $article, 'Premier commentaire de test.', true);
        $comment2 = $this->createComment($user, $article, 'Deuxieme commentaire de test.', true);

        // Forcer des timestamps différents pour garantir l'ordre
        $em = $this->getEntityManager();
        $em->getConnection()->executeStatement(
            'UPDATE comment SET created_at = :date WHERE id = :id',
            ['date' => '2025-01-01 10:00:00', 'id' => $comment1->getId()],
        );
        $em->getConnection()->executeStatement(
            'UPDATE comment SET created_at = :date WHERE id = :id',
            ['date' => '2025-01-02 10:00:00', 'id' => $comment2->getId()],
        );

        $em->clear();

        $results = $this->repository->findByArticle($article);

        self::assertCount(2, $results);
        self::assertSame('Deuxieme commentaire de test.', $results[0]->getContent());
    }

    public function testFindByArticleEmptyWhenNoComments(): void
    {
        $article = $this->createArticle();

        $results = $this->repository->findByArticle($article);

        self::assertCount(0, $results);
    }

    public function testFindByUserReturnsAllComments(): void
    {
        $user = $this->createActiveUser();
        $article = $this->createArticle();

        $this->createComment($user, $article, 'Commentaire approuvé long.', true);
        $this->createComment($user, $article, 'Commentaire non approuvé long.', false);

        $results = $this->repository->findByUser($user);

        self::assertCount(2, $results);
    }

    public function testFindByUserRespectsLimit(): void
    {
        $user = $this->createActiveUser();
        $article = $this->createArticle();

        for ($i = 1; $i <= 5; ++$i) {
            $this->createComment($user, $article, "Commentaire numéro $i assez long.", true);
        }

        $results = $this->repository->findByUser($user, 3);

        self::assertCount(3, $results);
    }

    public function testFindApprovedByUserReturnsOnlyApproved(): void
    {
        $user = $this->createActiveUser();
        $article = $this->createArticle();

        $this->createComment($user, $article, 'Commentaire approuvé un.', true);
        $this->createComment($user, $article, 'Commentaire non approuvé un.', false);

        $results = $this->repository->findApprovedByUser($user);

        self::assertCount(1, $results);
        self::assertTrue($results[0]->isApproved());
    }

    public function testFindApprovedByUserRespectsLimit(): void
    {
        $user = $this->createActiveUser();
        $article = $this->createArticle();

        for ($i = 1; $i <= 5; ++$i) {
            $this->createComment($user, $article, "Commentaire approuvé num $i.", true);
        }

        $results = $this->repository->findApprovedByUser($user, 2);

        self::assertCount(2, $results);
    }
}
