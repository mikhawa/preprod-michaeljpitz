<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Rating;
use PHPUnit\Framework\TestCase;

class RatingTest extends TestCase
{
    private Rating $rating;

    protected function setUp(): void
    {
        $this->rating = new Rating();
    }

    public function testSetCreatedAtValue(): void
    {
        self::assertNull($this->rating->getCreatedAt());

        $this->rating->setCreatedAtValue();

        self::assertInstanceOf(\DateTimeImmutable::class, $this->rating->getCreatedAt());
    }

    public function testToStringWithRating(): void
    {
        $this->rating->setRating(4);

        self::assertSame('4/5', (string) $this->rating);
    }

    public function testToStringWithoutRating(): void
    {
        self::assertSame('0/5', (string) $this->rating);
    }
}
