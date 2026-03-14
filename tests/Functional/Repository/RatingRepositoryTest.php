<?php

declare(strict_types=1);

namespace App\Tests\Functional\Repository;

use App\Repository\RatingRepository;
use App\Tests\Functional\TestDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class RatingRepositoryTest extends KernelTestCase
{
    use TestDatabaseTrait;

    private RatingRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->cleanDatabase();
        $this->repository = static::getContainer()->get(RatingRepository::class);
    }

    public function testFindByUserAndArticleFound(): void
    {
        $user = $this->createActiveUser();
        $article = $this->createArticle();
        $this->createRating($user, $article, 4);

        $result = $this->repository->findByUserAndArticle($user, $article);

        self::assertNotNull($result);
        self::assertSame(4, $result->getRating());
    }

    public function testFindByUserAndArticleNotFound(): void
    {
        $user = $this->createActiveUser();
        $article = $this->createArticle();

        $result = $this->repository->findByUserAndArticle($user, $article);

        self::assertNull($result);
    }

    public function testGetAverageRatingForArticle(): void
    {
        $user1 = $this->createActiveUser('user1@test.com', 'password123', 'userone');
        $user2 = $this->createActiveUser('user2@test.com', 'password123', 'usertwo');
        $article = $this->createArticle();

        $this->createRating($user1, $article, 3);
        $this->createRating($user2, $article, 5);

        $average = $this->repository->getAverageRatingForArticle($article);

        self::assertSame(4.0, $average);
    }

    public function testGetAverageRatingForArticleRounding(): void
    {
        $user1 = $this->createActiveUser('user1@test.com', 'password123', 'userone');
        $user2 = $this->createActiveUser('user2@test.com', 'password123', 'usertwo');
        $user3 = $this->createActiveUser('user3@test.com', 'password123', 'userthree');
        $article = $this->createArticle();

        $this->createRating($user1, $article, 3);
        $this->createRating($user2, $article, 4);
        $this->createRating($user3, $article, 5);

        $average = $this->repository->getAverageRatingForArticle($article);

        self::assertSame(4.0, $average);
    }

    public function testGetAverageRatingForArticleNullWhenNoRatings(): void
    {
        $article = $this->createArticle();

        $average = $this->repository->getAverageRatingForArticle($article);

        self::assertNull($average);
    }
}
