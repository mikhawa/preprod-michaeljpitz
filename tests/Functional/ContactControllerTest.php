<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ContactControllerTest extends WebTestCase
{
    use TestDatabaseTrait;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->cleanDatabase();
    }

    public function testContactPageIsAccessible(): void
    {
        $this->client->request('GET', '/contact');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Contact');
    }

    public function testContactFormSubmissionValid(): void
    {
        $this->createAdminUser();

        $crawler = $this->client->request('GET', '/contact');
        $form = $crawler->selectButton('Envoyer le message')->form([
            'contact[name]' => 'Jean Dupont',
            'contact[email]' => 'jean@example.com',
            'contact[message]' => 'Ceci est un message de test suffisamment long pour la validation.',
        ]);

        $this->client->submit($form);

        self::assertResponseRedirects('/contact');
        $this->client->followRedirect();
        self::assertSelectorExists('.bg-green-100,.text-green-700,[role="alert"]');
    }

    public function testContactFormInvalidEmail(): void
    {
        $crawler = $this->client->request('GET', '/contact');
        $form = $crawler->selectButton('Envoyer le message')->form([
            'contact[name]' => 'Jean Dupont',
            'contact[email]' => 'email-invalide',
            'contact[message]' => 'Ceci est un message de test suffisamment long pour la validation.',
        ]);

        $this->client->submit($form);

        self::assertResponseStatusCodeSame(422);
    }

    public function testContactFormEmptyName(): void
    {
        $crawler = $this->client->request('GET', '/contact');
        $form = $crawler->selectButton('Envoyer le message')->form([
            'contact[name]' => '',
            'contact[email]' => 'jean@example.com',
            'contact[message]' => 'Ceci est un message de test suffisamment long pour la validation.',
        ]);

        $this->client->submit($form);

        self::assertResponseStatusCodeSame(422);
    }

    public function testContactFormEmptyMessage(): void
    {
        $crawler = $this->client->request('GET', '/contact');
        $form = $crawler->selectButton('Envoyer le message')->form([
            'contact[name]' => 'Jean Dupont',
            'contact[email]' => 'jean@example.com',
            'contact[message]' => '',
        ]);

        $this->client->submit($form);

        self::assertResponseStatusCodeSame(422);
    }

    public function testContactFormShortMessage(): void
    {
        $crawler = $this->client->request('GET', '/contact');
        $form = $crawler->selectButton('Envoyer le message')->form([
            'contact[name]' => 'Jean Dupont',
            'contact[email]' => 'jean@example.com',
            'contact[message]' => 'Court',
        ]);

        $this->client->submit($form);

        self::assertResponseStatusCodeSame(422);
    }
}
