<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Comment;
use PHPUnit\Framework\TestCase;

class CommentTest extends TestCase
{
    private Comment $comment;

    protected function setUp(): void
    {
        $this->comment = new Comment();
    }

    public function testSetCreatedAtValue(): void
    {
        self::assertNull($this->comment->getCreatedAt());

        $this->comment->setCreatedAtValue();

        self::assertInstanceOf(\DateTimeImmutable::class, $this->comment->getCreatedAt());
    }

    public function testIsApprovedDefaultFalse(): void
    {
        self::assertFalse($this->comment->isApproved());
    }

    public function testSetIsApproved(): void
    {
        $this->comment->setIsApproved(true);
        self::assertTrue($this->comment->isApproved());
    }

    public function testToStringTruncatesAt50Chars(): void
    {
        $longContent = str_repeat('a', 100);
        $this->comment->setContent($longContent);

        $result = (string) $this->comment;

        self::assertSame(str_repeat('a', 50).'...', $result);
    }

    public function testToStringShortContent(): void
    {
        $this->comment->setContent('Court');

        self::assertSame('Court...', (string) $this->comment);
    }
}
