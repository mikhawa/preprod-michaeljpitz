<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegistrationControllerTest extends WebTestCase
{
    use TestDatabaseTrait;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->cleanDatabase();
    }

    public function testRegistrationPageIsAccessible(): void
    {
        $this->client->request('GET', '/inscription');

        self::assertResponseIsSuccessful();
    }

    public function testRegistrationWithValidData(): void
    {
        $crawler = $this->client->request('GET', '/inscription');

        $form = $crawler->filter('form[name="registration"]')->form([
            'registration[email]' => 'nouveau@test.com',
            'registration[userName]' => 'nouveauuser',
            'registration[plainPassword][first]' => 'password123',
            'registration[plainPassword][second]' => 'password123',
        ]);
        $this->client->submit($form);

        self::assertResponseRedirects('/connexion');

        /** @var UserRepository $userRepo */
        $userRepo = static::getContainer()->get(UserRepository::class);
        $user = $userRepo->findOneBy(['email' => 'nouveau@test.com']);

        self::assertNotNull($user);
        self::assertSame(0, $user->getStatus());
        self::assertNotNull($user->getActivationToken());

        $this->client->followRedirect();
        self::assertSelectorExists('.bg-green-100');
    }

    public function testRegistrationWithInvalidEmail(): void
    {
        $crawler = $this->client->request('GET', '/inscription');

        $form = $crawler->filter('form[name="registration"]')->form([
            'registration[email]' => 'pas-un-email',
            'registration[userName]' => 'nouveauuser',
            'registration[plainPassword][first]' => 'password123',
            'registration[plainPassword][second]' => 'password123',
        ]);
        $this->client->submit($form);

        self::assertResponseIsUnprocessable();
    }

    public function testRegistrationWithShortPassword(): void
    {
        $crawler = $this->client->request('GET', '/inscription');

        $form = $crawler->filter('form[name="registration"]')->form([
            'registration[email]' => 'nouveau@test.com',
            'registration[userName]' => 'nouveauuser',
            'registration[plainPassword][first]' => 'court',
            'registration[plainPassword][second]' => 'court',
        ]);
        $this->client->submit($form);

        self::assertResponseIsUnprocessable();
    }

    public function testRegistrationRedirectsIfAuthenticated(): void
    {
        $user = $this->createActiveUser();

        $this->client->loginUser($user);
        $this->client->request('GET', '/inscription');

        self::assertResponseRedirects('/');
    }

    public function testActivationWithValidToken(): void
    {
        $this->createInactiveUser('toactivate@test.com', 'password123', 'toactivate', 'mon-token-activation');

        $this->client->request('GET', '/activation/mon-token-activation');

        self::assertResponseRedirects('/connexion');

        /** @var UserRepository $userRepo */
        $userRepo = static::getContainer()->get(UserRepository::class);
        $user = $userRepo->findOneBy(['email' => 'toactivate@test.com']);

        self::assertNotNull($user);
        self::assertSame(1, $user->getStatus());
        self::assertNull($user->getActivationToken());
    }

    public function testActivationWithInvalidToken(): void
    {
        $this->client->request('GET', '/activation/token-inexistant');

        self::assertResponseRedirects('/connexion');
        $this->client->followRedirect();
        self::assertSelectorExists('.bg-red-100');
    }

    public function testActivationWithExpiredToken(): void
    {
        $this->createUser(
            email: 'expired@test.com',
            password: 'password123',
            userName: 'expireduser',
            status: 0,
            activationToken: 'expired-token',
            createdAt: new \DateTimeImmutable('-49 hours'),
        );

        $this->client->request('GET', '/activation/expired-token');

        self::assertResponseRedirects('/inscription');
        $this->client->followRedirect();
        self::assertSelectorExists('.bg-red-100');
    }

    public function testActivationOfAlreadyActiveAccount(): void
    {
        $this->createUser(
            email: 'already@test.com',
            password: 'password123',
            userName: 'alreadyactive',
            status: 1,
            activationToken: 'already-active-token',
        );

        $this->client->request('GET', '/activation/already-active-token');

        self::assertResponseRedirects('/connexion');
        $this->client->followRedirect();
        self::assertSelectorExists('.bg-blue-100');
    }
}
