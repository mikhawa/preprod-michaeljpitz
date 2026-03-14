<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityHeadersTest extends WebTestCase
{
    public function testXContentTypeOptionsHeader(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('X-Content-Type-Options', 'nosniff');
    }

    public function testXFrameOptionsHeader(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        self::assertResponseHeaderSame('X-Frame-Options', 'SAMEORIGIN');
    }

    public function testContentSecurityPolicyHeader(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $csp = $client->getResponse()->headers->get('Content-Security-Policy');
        self::assertNotNull($csp);
        self::assertStringContainsString("default-src 'self'", $csp);
    }

    public function testReferrerPolicyHeader(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        self::assertResponseHeaderSame('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function testPermissionsPolicyHeader(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $policy = $client->getResponse()->headers->get('Permissions-Policy');
        self::assertNotNull($policy);
        self::assertStringContainsString('camera=()', $policy);
        self::assertStringContainsString('microphone=()', $policy);
        self::assertStringContainsString('geolocation=()', $policy);
    }

    public function testXXssProtectionHeader(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        self::assertResponseHeaderSame('X-XSS-Protection', '1; mode=block');
    }
}
