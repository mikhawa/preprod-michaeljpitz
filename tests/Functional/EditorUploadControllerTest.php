<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class EditorUploadControllerTest extends WebTestCase
{
    use TestDatabaseTrait;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->cleanDatabase();
    }

    public function testUploadRequiresAdmin(): void
    {
        $user = $this->createActiveUser();
        $this->client->loginUser($user);

        $this->client->request('POST', '/admin/editor/upload', [], [], [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testUploadRequiresAuthentication(): void
    {
        $this->client->request('POST', '/admin/editor/upload', [], [], [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ]);

        self::assertResponseRedirects();
    }

    public function testUploadRejectsNonAjaxRequest(): void
    {
        $admin = $this->createAdminUser();
        $this->client->loginUser($admin);

        $this->client->request('POST', '/admin/editor/upload');

        self::assertResponseStatusCodeSame(403);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertSame('Requête non autorisée.', $data['error']);
    }

    public function testUploadRejectsWithoutFile(): void
    {
        $admin = $this->createAdminUser();
        $this->client->loginUser($admin);

        $this->client->request('POST', '/admin/editor/upload', [], [], [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ]);

        self::assertResponseStatusCodeSame(400);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertStringContainsString('Aucun fichier', $data['error']);
    }

    public function testUploadRejectsInvalidMimeType(): void
    {
        $admin = $this->createAdminUser();
        $this->client->loginUser($admin);

        $tempFile = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($tempFile, 'contenu texte');

        $uploadedFile = new UploadedFile(
            $tempFile,
            'test.txt',
            'text/plain',
            null,
            true,
        );

        $this->client->request('POST', '/admin/editor/upload', [], ['file' => $uploadedFile], [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ]);

        self::assertResponseStatusCodeSame(400);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertStringContainsString('Type de fichier non autorisé', $data['error']);
    }

    public function testUploadAcceptsValidJpeg(): void
    {
        $admin = $this->createAdminUser();
        $this->client->loginUser($admin);

        // Créer une image JPEG valide
        $image = imagecreatetruecolor(100, 100);
        $tempFile = tempnam(sys_get_temp_dir(), 'test').'.jpg';
        imagejpeg($image, $tempFile);
        imagedestroy($image);

        $uploadedFile = new UploadedFile(
            $tempFile,
            'test.jpg',
            'image/jpeg',
            null,
            true,
        );

        $this->client->request('POST', '/admin/editor/upload', [], ['file' => $uploadedFile], [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ]);

        self::assertResponseIsSuccessful();
        $content = json_decode($this->client->getResponse()->getContent(), true);
        self::assertArrayHasKey('url', $content);
        self::assertStringContainsString('/uploads/articles/content/', $content['url']);
    }
}
