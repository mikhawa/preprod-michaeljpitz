<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Service\TurnstileValidator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;

class TurnstileLoginSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly TurnstileValidator $turnstileValidator,
        private readonly RequestStack $requestStack,
        #[Autowire('%env(TURNSTILE_SITE_KEY)%')]
        private readonly string $turnstileSiteKey,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CheckPassportEvent::class => ['checkTurnstile', 10],
        ];
    }

    public function checkTurnstile(CheckPassportEvent $event): void
    {
        $request = $this->requestStack->getCurrentRequest();

        if (null === $request) {
            return;
        }

        // Uniquement sur la route de connexion
        if ('/connexion' !== $request->getPathInfo()) {
            return;
        }

        // Ne valider que si la clé de site est réelle (pas de test)
        $turnstileEnabled = !empty($this->turnstileSiteKey)
            && 'YOUR_TURNSTILE_SITE_KEY' !== $this->turnstileSiteKey
            && !str_starts_with($this->turnstileSiteKey, '1x00000000000000000000');

        if (!$turnstileEnabled) {
            return;
        }

        $token = $request->request->getString('cf-turnstile-response');

        if (!$this->turnstileValidator->validate($token, $request->getClientIp())) {
            throw new CustomUserMessageAuthenticationException('La vérification anti-robot a échoué. Veuillez réessayer.');
        }
    }
}
