<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    use TestDatabaseTrait;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->cleanDatabase();
    }

    public function testLoginPageIsAccessible(): void
    {
        $this->client->request('GET', '/connexion');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Connexion');
    }

    public function testLoginWithValidCredentials(): void
    {
        $this->createActiveUser('user@test.com', 'password123', 'testlogin');

        $this->client->request('GET', '/connexion');
        $this->client->submitForm('Se connecter', [
            '_username' => 'user@test.com',
            '_password' => 'password123',
        ]);

        self::assertResponseRedirects('/profil');
    }

    public function testLoginWithInvalidCredentials(): void
    {
        $this->createActiveUser('user@test.com', 'password123', 'testlogin');

        $this->client->request('GET', '/connexion');
        $this->client->submitForm('Se connecter', [
            '_username' => 'user@test.com',
            '_password' => 'mauvais_mot_de_passe',
        ]);

        self::assertResponseRedirects('/connexion');
        $this->client->followRedirect();
        self::assertSelectorExists('.bg-red-100');
    }

    public function testLoginWithInactiveAccount(): void
    {
        $this->createInactiveUser('inactive@test.com', 'password123', 'inactivelogin');

        $this->client->request('GET', '/connexion');
        $this->client->submitForm('Se connecter', [
            '_username' => 'inactive@test.com',
            '_password' => 'password123',
        ]);

        self::assertResponseRedirects('/connexion');
        $this->client->followRedirect();
        self::assertSelectorTextContains('.bg-red-100', 'pas encore activ');
    }

    public function testLoginWithBannedAccount(): void
    {
        $this->createBannedUser('banned@test.com', 'password123', 'bannedlogin');

        $this->client->request('GET', '/connexion');
        $this->client->submitForm('Se connecter', [
            '_username' => 'banned@test.com',
            '_password' => 'password123',
        ]);

        self::assertResponseRedirects('/connexion');
        $this->client->followRedirect();
        self::assertSelectorTextContains('.bg-red-100', 'banni');
    }

    public function testLogout(): void
    {
        $user = $this->createActiveUser();

        $this->client->loginUser($user);
        $this->client->request('GET', '/deconnexion');

        self::assertResponseRedirects();
    }

    public function testLoginRedirectsIfAlreadyAuthenticated(): void
    {
        $user = $this->createActiveUser();

        $this->client->loginUser($user);
        $this->client->request('GET', '/connexion');

        self::assertResponseRedirects('/');
    }
}
