<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class TwoFactorGateSubscriber implements EventSubscriberInterface
{
    /** Routes accessibles même avec le flag 2fa_required actif */
    private const ALLOWED_ROUTES = [
        'app_two_factor',
        'app_logout',
    ];

    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly RouterInterface $router,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 10],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $token = $this->tokenStorage->getToken();
        if (null === $token || !$token->getUser()) {
            return;
        }

        $request = $event->getRequest();

        if (!$request->hasSession()) {
            return;
        }

        $session = $request->getSession();

        if (!$session->get('2fa_required', false)) {
            return;
        }

        $currentRoute = $request->attributes->get('_route');
        if (in_array($currentRoute, self::ALLOWED_ROUTES, true)) {
            return;
        }

        // Profiler Symfony et assets : on les laisse passer
        $pathInfo = $request->getPathInfo();
        if (str_starts_with($pathInfo, '/_') || str_starts_with($pathInfo, '/build/')) {
            return;
        }

        $event->setResponse(new RedirectResponse($this->router->generate('app_two_factor')));
    }
}
