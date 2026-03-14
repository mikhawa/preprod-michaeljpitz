<?php

declare(strict_types=1);

namespace App\Tests\Unit\Security;

use App\Entity\User;
use App\Security\UserChecker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\InMemoryUser;

class UserCheckerTest extends TestCase
{
    private UserChecker $userChecker;

    protected function setUp(): void
    {
        $this->userChecker = new UserChecker();
    }

    public function testCheckPreAuthInactiveUser(): void
    {
        $user = new User();
        $user->setStatus(0);

        $this->expectException(CustomUserMessageAccountStatusException::class);
        $this->expectExceptionMessage('pas encore activé');

        $this->userChecker->checkPreAuth($user);
    }

    public function testCheckPreAuthBannedUser(): void
    {
        $user = new User();
        $user->setStatus(2);

        $this->expectException(CustomUserMessageAccountStatusException::class);
        $this->expectExceptionMessage('banni');

        $this->userChecker->checkPreAuth($user);
    }

    public function testCheckPreAuthActiveUser(): void
    {
        $user = new User();
        $user->setStatus(1);

        $this->userChecker->checkPreAuth($user);

        $this->addToAssertionCount(1);
    }

    public function testCheckPreAuthNonUserInstance(): void
    {
        $user = new InMemoryUser('test', 'password');

        $this->userChecker->checkPreAuth($user);

        $this->addToAssertionCount(1);
    }

    public function testCheckPostAuthDoesNothing(): void
    {
        $user = new User();
        $user->setStatus(1);

        $this->userChecker->checkPostAuth($user);

        $this->addToAssertionCount(1);
    }
}
