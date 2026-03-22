<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class TwoFactorLoginSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly RequestStack $requestStack,
        #[Autowire('%env(EMAIL_NOTIFICATIONS_FROM)%')]
        private readonly string $emailFrom,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccess',
        ];
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getAuthenticatedToken()->getUser();

        if (!$user instanceof User) {
            return;
        }

        $session = $this->requestStack->getSession();

        // Génération d'un code OTP à 6 chiffres
        $code = (string) random_int(100000, 999999);

        // Stockage du hash (jamais le code en clair en session)
        $session->set('2fa_code_hash', hash('sha256', $code));
        $session->set('2fa_expires_at', time() + 600); // 10 minutes
        $session->set('2fa_attempts', 0);
        $session->set('2fa_required', true);
        $session->set('2fa_user_email', $user->getEmail());

        $this->mailer->send(
            (new TemplatedEmail())
                ->from(new Address($this->emailFrom, 'CV Mikhawa'))
                ->to((string) $user->getEmail())
                ->subject('Votre code de vérification')
                ->htmlTemplate('email/two_factor_code.html.twig')
                ->context([
                    'user' => $user,
                    'code' => $code,
                ])
        );
    }
}
