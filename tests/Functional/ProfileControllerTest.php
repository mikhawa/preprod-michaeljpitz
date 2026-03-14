<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProfileControllerTest extends WebTestCase
{
    use TestDatabaseTrait;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->cleanDatabase();
    }

    public function testProfileRequiresAuthentication(): void
    {
        $this->client->request('GET', '/profil');

        self::assertResponseRedirects();
        self::assertStringContainsString('connexion', (string) $this->client->getResponse()->headers->get('Location'));
    }

    public function testProfilePageIsAccessible(): void
    {
        $user = $this->createActiveUser();
        $this->client->loginUser($user);

        $this->client->request('GET', '/profil');

        self::assertResponseIsSuccessful();
    }

    public function testProfileDisplaysUserInfo(): void
    {
        $user = $this->createActiveUser('info@test.com', 'password123', 'infouser');
        $this->client->loginUser($user);

        $crawler = $this->client->request('GET', '/profil');

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('infouser', $crawler->text());
    }

    public function testProfileUpdateBiography(): void
    {
        $user = $this->createActiveUser('bio@test.com', 'password123', 'bioupdate');
        $this->client->loginUser($user);

        $crawler = $this->client->request('GET', '/profil');
        $form = $crawler->selectButton('Enregistrer les modifications')->form([
            'profile[biography]' => 'Ma nouvelle biographie de test.',
        ]);

        $this->client->submit($form);

        self::assertResponseRedirects('/profil');
        $this->client->followRedirect();

        $em = $this->getEntityManager();
        $em->clear();
        $updatedUser = $em->getRepository(\App\Entity\User::class)->find($user->getId());
        self::assertSame('Ma nouvelle biographie de test.', $updatedUser->getBiography());
    }

    public function testProfileUpdateExternalLinks(): void
    {
        $user = $this->createActiveUser('links@test.com', 'password123', 'linkuser');
        $this->client->loginUser($user);

        $crawler = $this->client->request('GET', '/profil');
        $form = $crawler->selectButton('Enregistrer les modifications')->form([
            'profile[externalLink1]' => 'https://github.com/testuser',
        ]);

        $this->client->submit($form);

        self::assertResponseRedirects('/profil');

        $em = $this->getEntityManager();
        $em->clear();
        $updatedUser = $em->getRepository(\App\Entity\User::class)->find($user->getId());
        self::assertSame('https://github.com/testuser', $updatedUser->getExternalLink1());
    }

    public function testProfileInvalidUrlReturns422(): void
    {
        $user = $this->createActiveUser('invalid@test.com', 'password123', 'invalidurl');
        $this->client->loginUser($user);

        $crawler = $this->client->request('GET', '/profil');
        $form = $crawler->selectButton('Enregistrer les modifications')->form([
            'profile[externalLink1]' => 'pas-une-url-valide',
        ]);

        $this->client->submit($form);

        self::assertResponseStatusCodeSame(422);
    }
}
