<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PublicProfileControllerTest extends WebTestCase
{
    use TestDatabaseTrait;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->cleanDatabase();
    }

    public function testActiveUserProfileIsAccessible(): void
    {
        $this->createActiveUser('active@test.com', 'password123', 'activeprofile');

        $this->client->request('GET', '/utilisateur/activeprofile');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'activeprofile');
    }

    public function testInactiveUserProfileReturns404(): void
    {
        $this->createInactiveUser('inactive@test.com', 'password123', 'inactiveprofile');

        $this->client->request('GET', '/utilisateur/inactiveprofile');

        self::assertResponseStatusCodeSame(404);
    }

    public function testBannedUserProfileReturns404(): void
    {
        $this->createBannedUser('banned@test.com', 'password123', 'bannedprofile');

        $this->client->request('GET', '/utilisateur/bannedprofile');

        self::assertResponseStatusCodeSame(404);
    }

    public function testNonExistentUserReturns404(): void
    {
        $this->client->request('GET', '/utilisateur/utilisateur_inexistant');

        self::assertResponseStatusCodeSame(404);
    }

    public function testApprovedCommentsAreDisplayed(): void
    {
        $user = $this->createActiveUser('commenter@test.com', 'password123', 'commenter');
        $article = $this->createArticle();

        $this->createComment($user, $article, 'Commentaire approuvé visible.', true);
        $this->createComment($user, $article, 'Commentaire non approuvé caché.', false);

        $crawler = $this->client->request('GET', '/utilisateur/commenter');

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Commentaires (1)', $crawler->text());
    }

    public function testBiographyIsDisplayed(): void
    {
        $user = $this->createActiveUser('bio@test.com', 'password123', 'biouser');
        $user->setBiography('Je suis développeur PHP passionné.');
        $this->getEntityManager()->flush();

        $crawler = $this->client->request('GET', '/utilisateur/biouser');

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('développeur PHP passionné', $crawler->text());
    }

    public function testNoCommentsMessage(): void
    {
        $this->createActiveUser('nocomments@test.com', 'password123', 'nocomments');

        $crawler = $this->client->request('GET', '/utilisateur/nocomments');

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Aucun commentaire public', $crawler->text());
    }
}
