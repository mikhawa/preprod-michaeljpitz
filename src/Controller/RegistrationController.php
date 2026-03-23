<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationType;
use App\Repository\UserRepository;
use App\Service\TurnstileValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RegistrationController extends AbstractController
{
    public function __construct(
        private readonly TurnstileValidator $turnstileValidator,
        #[Autowire('%env(TURNSTILE_SITE_KEY)%')]
        private readonly string $turnstileSiteKey,
        #[Autowire('%env(EMAIL_NOTIFICATIONS_FROM)%')]
        private readonly string $emailFrom,
        #[Autowire('%env(ADMIN_EMAIL)%')]
        private readonly string $adminEmail,
    ) {
    }

    #[Route('/inscription', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $turnstileEnabled = !empty($this->turnstileSiteKey)
                && 'YOUR_TURNSTILE_SITE_KEY' !== $this->turnstileSiteKey
                && !str_starts_with($this->turnstileSiteKey, '1x00000000000000000000');

            if ($turnstileEnabled) {
                $turnstileToken = $request->request->getString('cf-turnstile-response');

                if (!$this->turnstileValidator->validate($turnstileToken, $request->getClientIp())) {
                    $this->addFlash('error', 'La vérification anti-robot a échoué. Veuillez réessayer.');

                    return $this->render('security/register.html.twig', [
                        'registrationForm' => $form,
                        'turnstileSiteKey' => $this->turnstileSiteKey,
                    ], new Response(status: Response::HTTP_UNPROCESSABLE_ENTITY));
                }
            }

            $user->setPassword(
                $passwordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData(),
                ),
            );
            $user->setActivationToken(bin2hex(random_bytes(32)));

            $entityManager->persist($user);
            $entityManager->flush();

            $activationUrl = $this->generateUrl(
                'app_activate_account',
                ['token' => $user->getActivationToken()],
                UrlGeneratorInterface::ABSOLUTE_URL,
            );

            $email = (new TemplatedEmail())
                ->from(new Address($this->emailFrom, 'MichaelJPitz.com'))
                ->to((string) $user->getEmail())
                ->subject('Activez votre compte')
                ->htmlTemplate('email/activation.html.twig')
                ->context([
                    'user' => $user,
                    'activation_url' => $activationUrl,
                ]);

            $mailer->send($email);

            $adminNotification = (new TemplatedEmail())
                ->from(new Address($this->emailFrom, 'MichaelJPitz.com'))
                ->to($this->adminEmail)
                ->subject('Nouvelle inscription - '.$user->getUserName())
                ->htmlTemplate('email/new_user_notification.html.twig')
                ->context([
                    'userName' => $user->getUserName(),
                    'userEmail' => $user->getEmail(),
                    'registrationDate' => $user->getCreatedAt() ?? new \DateTimeImmutable(),
                ]);

            $mailer->send($adminNotification);

            $this->addFlash('success', 'Votre compte a été créé. Un email de validation vous a été envoyé. Vous avez 48 heures pour activer votre compte.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/register.html.twig', [
            'registrationForm' => $form,
            'turnstileSiteKey' => $this->turnstileSiteKey,
        ]);
    }

    #[Route('/activation/{token}', name: 'app_activate_account')]
    public function activate(
        string $token,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
    ): Response {
        $user = $userRepository->findOneBy(['activationToken' => $token]);

        if (null === $user) {
            $this->addFlash('error', 'Lien d\'activation invalide.');

            return $this->redirectToRoute('app_login');
        }

        if (1 === $user->getStatus()) {
            $this->addFlash('info', 'Votre compte est déjà activé.');

            return $this->redirectToRoute('app_login');
        }

        $createdAt = $user->getCreatedAt();

        if (null === $createdAt || $createdAt < new \DateTimeImmutable('-48 hours')) {
            $this->addFlash('error', 'Le lien d\'activation a expiré. Veuillez vous réinscrire.');

            return $this->redirectToRoute('app_register');
        }

        $user->setStatus(1);
        $user->setActivationToken(null);
        $entityManager->flush();

        $adminNotification = (new TemplatedEmail())
            ->from(new Address($this->emailFrom, 'MichaelJPitz.com'))
            ->to($this->adminEmail)
            ->subject('Compte activé - '.$user->getUserName())
            ->htmlTemplate('email/user_activated_notification.html.twig')
            ->context([
                'userName' => $user->getUserName(),
                'userEmail' => $user->getEmail(),
                'activationDate' => new \DateTimeImmutable(),
            ]);

        $mailer->send($adminNotification);

        $this->addFlash('success', 'Votre compte a été activé avec succès. Vous pouvez maintenant vous connecter.');

        return $this->redirectToRoute('app_login');
    }
}
