<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminAccessTest extends WebTestCase
{
    use TestDatabaseTrait;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->cleanDatabase();
    }

    public function testAdminRequiresAuthentication(): void
    {
        $this->client->request('GET', '/admin');

        self::assertResponseRedirects();
        self::assertStringContainsString('connexion', (string) $this->client->getResponse()->headers->get('Location'));
    }

    public function testAdminForbiddenForRegularUser(): void
    {
        $user = $this->createActiveUser();

        $this->client->loginUser($user);
        $this->client->request('GET', '/admin');

        self::assertResponseStatusCodeSame(403);
    }

    public function testAdminAccessibleForAdmin(): void
    {
        $admin = $this->createAdminUser();

        $this->client->loginUser($admin);
        $this->client->request('GET', '/admin');

        $statusCode = $this->client->getResponse()->getStatusCode();
        self::assertTrue(
            200 === $statusCode || 302 === $statusCode,
            sprintf('Code HTTP attendu : 200 ou 302, recu : %d', $statusCode),
        );
    }
}
