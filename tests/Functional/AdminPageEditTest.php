<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Page;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminPageEditTest extends WebTestCase
{
    use TestDatabaseTrait;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->cleanDatabase();
    }

    private function createPage(string $title, string $slug, string $content): Page
    {
        $em = $this->getEntityManager();

        $page = new Page();
        $page->setTitle($title);
        $page->setSlug($slug);
        $page->setContent($content);

        $em->persist($page);
        $em->flush();

        return $page;
    }

    public function testAdminCanAccessPageEditForm(): void
    {
        $admin = $this->createAdminUser();
        $page = $this->createPage('Politique de confidentialité (RGPD)', 'rgpd', '<h2>Collecte des données</h2><p>Contenu RGPD.</p>');

        $this->client->loginUser($admin);
        $this->client->request('GET', '/admin/page/'.$page->getId().'/edit');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form');
    }

    public function testAdminCanAccessPageIndex(): void
    {
        $admin = $this->createAdminUser();
        $this->createPage('CV', 'cv', '<p>Mon CV</p>');
        $this->createPage('RGPD', 'rgpd', '<p>RGPD</p>');

        $this->client->loginUser($admin);
        $this->client->request('GET', '/admin/page');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', 'CV');
        self::assertSelectorTextContains('body', 'RGPD');
    }
}
