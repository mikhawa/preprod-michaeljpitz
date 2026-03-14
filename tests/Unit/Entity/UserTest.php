<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Comment;
use App\Entity\Rating;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        $this->user = new User();
    }

    public function testGetRolesContainsRoleUser(): void
    {
        self::assertContains('ROLE_USER', $this->user->getRoles());
    }

    public function testGetRolesWithAdditionalRole(): void
    {
        $this->user->setRoles(['ROLE_ADMIN']);
        $roles = $this->user->getRoles();

        self::assertContains('ROLE_USER', $roles);
        self::assertContains('ROLE_ADMIN', $roles);
    }

    public function testGetRolesAreUnique(): void
    {
        $this->user->setRoles(['ROLE_USER', 'ROLE_USER', 'ROLE_ADMIN']);
        $roles = $this->user->getRoles();

        self::assertCount(2, $roles);
    }

    public function testSetCreatedAtValue(): void
    {
        self::assertNull($this->user->getCreatedAt());

        $this->user->setCreatedAtValue();

        self::assertInstanceOf(\DateTimeImmutable::class, $this->user->getCreatedAt());
    }

    public function testDefaultStatus(): void
    {
        self::assertSame(0, $this->user->getStatus());
    }

    public function testGetUserIdentifier(): void
    {
        $this->user->setEmail('test@example.com');

        self::assertSame('test@example.com', $this->user->getUserIdentifier());
    }

    public function testGetUserIdentifierWithoutEmail(): void
    {
        $this->expectException(\LogicException::class);
        $this->user->getUserIdentifier();
    }

    public function testEraseCredentials(): void
    {
        $this->user->setPlainPassword('secret123');
        self::assertSame('secret123', $this->user->getPlainPassword());

        $this->user->eraseCredentials();
        self::assertNull($this->user->getPlainPassword());
    }

    public function testToStringReturnsUserName(): void
    {
        $this->user->setUserName('johndoe');

        self::assertSame('johndoe', (string) $this->user);
    }

    public function testToStringReturnsEmptyWhenNoUserName(): void
    {
        self::assertSame('', (string) $this->user);
    }

    public function testAddAndRemoveComment(): void
    {
        $comment = new Comment();
        $this->user->addComment($comment);

        self::assertCount(1, $this->user->getComments());
        self::assertSame($this->user, $comment->getUser());

        $this->user->removeComment($comment);
        self::assertCount(0, $this->user->getComments());
    }

    public function testAddAndRemoveRating(): void
    {
        $rating = new Rating();
        $this->user->addRating($rating);

        self::assertCount(1, $this->user->getRatings());
        self::assertSame($this->user, $rating->getUser());

        $this->user->removeRating($rating);
        self::assertCount(0, $this->user->getRatings());
    }
}
