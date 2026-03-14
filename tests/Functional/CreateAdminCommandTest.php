<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class CreateAdminCommandTest extends KernelTestCase
{
    use TestDatabaseTrait;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->cleanDatabase();
    }

    public function testCreateAdminCreatesUser(): void
    {
        $application = new Application(self::$kernel);
        $command = $application->find('app:create-admin');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'email' => 'newadmin@test.com',
            'password' => 'securepassword123',
            'username' => 'newadmin',
        ]);

        $commandTester->assertCommandIsSuccessful();

        $em = $this->getEntityManager();
        $user = $em->getRepository(User::class)->findOneBy(['email' => 'newadmin@test.com']);

        self::assertNotNull($user);
        self::assertSame('newadmin', $user->getUserName());
    }

    public function testCreateAdminHasRoleAdmin(): void
    {
        $application = new Application(self::$kernel);
        $command = $application->find('app:create-admin');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'email' => 'roleadmin@test.com',
            'password' => 'securepassword123',
            'username' => 'roleadmin',
        ]);

        $em = $this->getEntityManager();
        $user = $em->getRepository(User::class)->findOneBy(['email' => 'roleadmin@test.com']);

        self::assertContains('ROLE_ADMIN', $user->getRoles());
    }

    public function testCreateAdminSuccessMessage(): void
    {
        $application = new Application(self::$kernel);
        $command = $application->find('app:create-admin');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'email' => 'msgadmin@test.com',
            'password' => 'securepassword123',
            'username' => 'msgadmin',
        ]);

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('créé avec succès', $output);
    }
}
