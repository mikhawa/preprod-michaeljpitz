<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Rating;
use PHPUnit\Framework\TestCase;

class ArticleTest extends TestCase
{
    private Article $article;

    protected function setUp(): void
    {
        $this->article = new Article();
    }

    public function testSetCreatedAtValue(): void
    {
        self::assertNull($this->article->getCreatedAt());

        $this->article->setCreatedAtValue();

        self::assertInstanceOf(\DateTimeImmutable::class, $this->article->getCreatedAt());
    }

    public function testSetUpdatedAtValue(): void
    {
        self::assertNull($this->article->getUpdatedAt());

        $this->article->setUpdatedAtValue();

        self::assertInstanceOf(\DateTimeImmutable::class, $this->article->getUpdatedAt());
    }

    public function testGetAverageRatingWithNoRatings(): void
    {
        self::assertNull($this->article->getAverageRating());
    }

    public function testGetAverageRatingWithOneRating(): void
    {
        $rating = new Rating();
        $rating->setRating(4);
        $this->article->addRating($rating);

        self::assertSame(4.0, $this->article->getAverageRating());
    }

    public function testGetAverageRatingWithMultipleRatings(): void
    {
        $rating1 = new Rating();
        $rating1->setRating(3);
        $this->article->addRating($rating1);

        $rating2 = new Rating();
        $rating2->setRating(5);
        $this->article->addRating($rating2);

        self::assertSame(4.0, $this->article->getAverageRating());
    }

    public function testGetAverageRatingRounding(): void
    {
        $rating1 = new Rating();
        $rating1->setRating(3);
        $this->article->addRating($rating1);

        $rating2 = new Rating();
        $rating2->setRating(4);
        $this->article->addRating($rating2);

        $rating3 = new Rating();
        $rating3->setRating(5);
        $this->article->addRating($rating3);

        self::assertSame(4.0, $this->article->getAverageRating());
    }

    public function testToStringReturnsTitle(): void
    {
        $this->article->setTitle('Mon Article');

        self::assertSame('Mon Article', (string) $this->article);
    }

    public function testToStringReturnsEmptyWhenNoTitle(): void
    {
        self::assertSame('', (string) $this->article);
    }

    public function testIsPublishedDefaultFalse(): void
    {
        self::assertFalse($this->article->isPublished());
    }

    public function testAddAndRemoveCategory(): void
    {
        $category = new Category();
        $category->setTitle('PHP');

        $this->article->addCategory($category);
        self::assertCount(1, $this->article->getCategories());

        $this->article->removeCategory($category);
        self::assertCount(0, $this->article->getCategories());
    }

    public function testAddCategoryDoesNotDuplicate(): void
    {
        $category = new Category();
        $category->setTitle('PHP');

        $this->article->addCategory($category);
        $this->article->addCategory($category);

        self::assertCount(1, $this->article->getCategories());
    }

    public function testAddAndRemoveComment(): void
    {
        $comment = new Comment();
        $this->article->addComment($comment);

        self::assertCount(1, $this->article->getComments());
        self::assertSame($this->article, $comment->getArticle());

        $this->article->removeComment($comment);
        self::assertCount(0, $this->article->getComments());
    }
}
