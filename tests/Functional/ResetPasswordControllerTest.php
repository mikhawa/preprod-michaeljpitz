<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ResetPasswordControllerTest extends WebTestCase
{
    use TestDatabaseTrait;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->cleanDatabase();
    }

    public function testForgotPasswordPageIsAccessible(): void
    {
        $this->client->request('GET', '/mot-de-passe-oublie');

        self::assertResponseIsSuccessful();
    }

    public function testForgotPasswordWithExistingEmail(): void
    {
        $this->createActiveUser('existing@test.com', 'password123', 'existinguser');

        $crawler = $this->client->request('GET', '/mot-de-passe-oublie');
        $form = $crawler->filter('form[name="reset_password_request"]')->form([
            'reset_password_request[email]' => 'existing@test.com',
        ]);
        $this->client->submit($form);

        self::assertResponseRedirects('/connexion');

        /** @var UserRepository $userRepo */
        $userRepo = static::getContainer()->get(UserRepository::class);
        $user = $userRepo->findOneBy(['email' => 'existing@test.com']);

        self::assertNotNull($user);
        self::assertNotNull($user->getResetPasswordToken());
        self::assertNotNull($user->getResetPasswordRequestedAt());
    }

    public function testForgotPasswordWithNonExistingEmail(): void
    {
        $crawler = $this->client->request('GET', '/mot-de-passe-oublie');
        $form = $crawler->filter('form[name="reset_password_request"]')->form([
            'reset_password_request[email]' => 'inexistant@test.com',
        ]);
        $this->client->submit($form);

        self::assertResponseRedirects('/connexion');
        $this->client->followRedirect();
        self::assertSelectorExists('.bg-green-100');
    }

    public function testResetPasswordWithValidToken(): void
    {
        // Le token stocké en BDD est le hash SHA-256 du token clair envoyé dans l'URL
        $this->createUser(
            email: 'reset@test.com',
            password: 'password123',
            userName: 'resetuser',
            status: 1,
            resetPasswordToken: hash('sha256', 'valid-reset-token'),
            resetPasswordRequestedAt: new \DateTimeImmutable('-30 minutes'),
        );

        $crawler = $this->client->request('GET', '/reinitialiser-mot-de-passe/valid-reset-token');
        self::assertResponseIsSuccessful();

        $form = $crawler->filter('form[name="reset_password"]')->form([
            'reset_password[plainPassword][first]' => 'nouveaumdp123',
            'reset_password[plainPassword][second]' => 'nouveaumdp123',
        ]);
        $this->client->submit($form);

        self::assertResponseRedirects('/connexion');

        /** @var UserRepository $userRepo */
        $userRepo = static::getContainer()->get(UserRepository::class);
        $user = $userRepo->findOneBy(['email' => 'reset@test.com']);

        self::assertNotNull($user);
        self::assertNull($user->getResetPasswordToken());
        self::assertNull($user->getResetPasswordRequestedAt());
    }

    public function testResetPasswordWithInvalidToken(): void
    {
        $this->client->request('GET', '/reinitialiser-mot-de-passe/token-inexistant');

        self::assertResponseRedirects('/connexion');
        $this->client->followRedirect();
        self::assertSelectorExists('.bg-red-100');
    }

    public function testResetPasswordWithExpiredToken(): void
    {
        // Le token stocké en BDD est le hash SHA-256 du token clair envoyé dans l'URL
        $this->createUser(
            email: 'expired@test.com',
            password: 'password123',
            userName: 'expiredreset',
            status: 1,
            resetPasswordToken: hash('sha256', 'expired-reset-token'),
            resetPasswordRequestedAt: new \DateTimeImmutable('-2 hours'),
        );

        $this->client->request('GET', '/reinitialiser-mot-de-passe/expired-reset-token');

        self::assertResponseRedirects('/mot-de-passe-oublie');
        $this->client->followRedirect();
        self::assertSelectorExists('.bg-red-100');
    }
}
