<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\ContactType;
use App\Repository\UserRepository;
use App\Service\TurnstileValidator;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Attribute\Route;

class ContactController extends AbstractController
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly UserRepository $userRepository,
        private readonly TurnstileValidator $turnstileValidator,
        #[Autowire('%env(TURNSTILE_SITE_KEY)%')]
        private readonly string $turnstileSiteKey,
        #[Autowire('%env(CONTACT_FALLBACK_EMAIL)%')]
        private readonly string $fallbackEmail,
    ) {
    }

    #[Route('/contact', name: 'app_contact', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Valider Turnstile uniquement si le widget est affiché (clé réelle, pas de test)
            $turnstileEnabled = !empty($this->turnstileSiteKey)
                && 'YOUR_TURNSTILE_SITE_KEY' !== $this->turnstileSiteKey
                && !str_starts_with($this->turnstileSiteKey, '1x00000000000000000000');

            if ($turnstileEnabled) {
                $turnstileToken = $request->request->getString('cf-turnstile-response');
                $clientIp = $request->getClientIp();

                if (!$this->turnstileValidator->validate($turnstileToken, $clientIp)) {
                    $this->addFlash('error', 'La vérification anti-robot a échoué. Veuillez réessayer.');

                    return $this->render('contact/index.html.twig', [
                        'contactForm' => $form,
                        'turnstileSiteKey' => $this->turnstileSiteKey,
                    ], new Response(status: Response::HTTP_UNPROCESSABLE_ENTITY));
                }
            }

            $data = $form->getData();
            $adminEmail = $this->getAdminEmail() ?? $this->fallbackEmail;

            $email = (new TemplatedEmail())
                ->from(new Address('contact@alpha1.michaeljpitz.com', 'CV Mikhawa - Contact'))
                ->to('contact@alpha1.michaeljpitz.com')
                ->replyTo(new Address($data['email'], $data['name']))
                ->subject('Nouveau message de contact - '.$data['name'])
                ->htmlTemplate('email/contact_notification.html.twig')
                ->context([
                    'contactName' => $data['name'],
                    'contactEmail' => $data['email'],
                    'contactMessage' => $data['message'],
                ]);

            $this->mailer->send($email);

            $this->addFlash('success', 'Votre message a bien été envoyé. Je vous répondrai dans les plus brefs délais.');

            return $this->redirectToRoute('app_contact');
        }

        return $this->render('contact/index.html.twig', [
            'contactForm' => $form,
            'turnstileSiteKey' => $this->turnstileSiteKey,
        ]);
    }

    private function getAdminEmail(): ?string
    {
        $admins = $this->userRepository->createQueryBuilder('u')
            ->where('u.roles LIKE :role')
            ->setParameter('role', '%ROLE_ADMIN%')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();

        if (empty($admins)) {
            return null;
        }

        return $admins[0]->getEmail();
    }
}
