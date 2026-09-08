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

    /** Chemins accessibles en fallback (si le routing n'est pas encore résolu) */
    private const ALLOWED_PATHS = [
        '/connexion/code-verification',
        '/deconnexion',
    ];

    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly RouterInterface $router,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // Priorité 5 : après RouterListener (8) et le Firewall (8), avant les contrôleurs
            KernelEvents::REQUEST => ['onKernelRequest', 5],
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

        $pathInfo = $request->getPathInfo();

        // Profiler Symfony et assets : on les laisse passer
        if (str_starts_with($pathInfo, '/_') || str_starts_with($pathInfo, '/build/')) {
            return;
        }

        // Vérification par nom de route (disponible après RouterListener)
        $currentRoute = $request->attributes->get('_route');
        if (null !== $currentRoute && in_array($currentRoute, self::ALLOWED_ROUTES, true)) {
            return;
        }

        // Fallback par chemin (sécurité si routing non encore résolu)
        if (in_array($pathInfo, self::ALLOWED_PATHS, true)) {
            return;
        }

        $event->setResponse(new RedirectResponse($this->router->generate('app_two_factor')));
    }
}
