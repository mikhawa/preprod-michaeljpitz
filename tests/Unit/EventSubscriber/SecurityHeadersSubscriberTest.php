<?php

declare(strict_types=1);

namespace App\Tests\Unit\EventSubscriber;

use App\EventSubscriber\SecurityHeadersSubscriber;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class SecurityHeadersSubscriberTest extends TestCase
{
    private SecurityHeadersSubscriber $subscriber;
    private SecurityHeadersSubscriber $subscriberProd;

    protected function setUp(): void
    {
        $this->subscriber = new SecurityHeadersSubscriber('test');
        $this->subscriberProd = new SecurityHeadersSubscriber('prod');
    }

    public function testGetSubscribedEvents(): void
    {
        $events = SecurityHeadersSubscriber::getSubscribedEvents();

        self::assertArrayHasKey(KernelEvents::RESPONSE, $events);
        self::assertSame('onKernelResponse', $events[KernelEvents::RESPONSE]);
    }

    public function testHeadersAddedOnMainRequest(): void
    {
        $kernel = $this->createStub(HttpKernelInterface::class);
        $request = new Request();
        $response = new Response();

        $event = new ResponseEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $response);
        $this->subscriber->onKernelResponse($event);

        self::assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
        self::assertSame('SAMEORIGIN', $response->headers->get('X-Frame-Options'));
        self::assertSame('1; mode=block', $response->headers->get('X-XSS-Protection'));
        self::assertSame('strict-origin-when-cross-origin', $response->headers->get('Referrer-Policy'));
        self::assertNotNull($response->headers->get('Permissions-Policy'));
        self::assertNotNull($response->headers->get('Content-Security-Policy'));
    }

    public function testHstsNotAddedOutsideProd(): void
    {
        $kernel = $this->createStub(HttpKernelInterface::class);
        $request = new Request();
        $response = new Response();

        $event = new ResponseEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $response);
        $this->subscriber->onKernelResponse($event);

        self::assertNull($response->headers->get('Strict-Transport-Security'));
    }

    public function testHstsAddedInProd(): void
    {
        $kernel = $this->createStub(HttpKernelInterface::class);
        $request = new Request();
        $response = new Response();

        $event = new ResponseEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $response);
        $this->subscriberProd->onKernelResponse($event);

        self::assertSame(
            'max-age=31536000; includeSubDomains',
            $response->headers->get('Strict-Transport-Security'),
        );
    }

    public function testHeadersNotAddedOnSubRequest(): void
    {
        $kernel = $this->createStub(HttpKernelInterface::class);
        $request = new Request();
        $response = new Response();

        $event = new ResponseEvent($kernel, $request, HttpKernelInterface::SUB_REQUEST, $response);
        $this->subscriber->onKernelResponse($event);

        self::assertNull($response->headers->get('X-Content-Type-Options'));
        self::assertNull($response->headers->get('X-XSS-Protection'));
    }
}
