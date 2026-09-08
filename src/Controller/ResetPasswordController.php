<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\ResetPasswordRequestType;
use App\Form\ResetPasswordType;
use App\Repository\UserRepository;
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

class ResetPasswordController extends AbstractController
{
    public function __construct(
        #[Autowire('%env(EMAIL_NOTIFICATIONS_FROM)%')]
        private readonly string $emailFrom,
    ) {
    }

    #[Route('/mot-de-passe-oublie', name: 'app_forgot_password')]
    public function forgotPassword(
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $form = $this->createForm(ResetPasswordRequestType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $user = $userRepository->findOneBy(['email' => $email]);

            // On envoie l'email uniquement si le compte existe et est actif (status = 1)
            // Mais on affiche toujours le même message pour ne pas révéler les comptes
            if (null !== $user && 1 === $user->getStatus()) {
                $token = bin2hex(random_bytes(32));
                // On stocke le hash en base, le token clair part uniquement dans l'email
                $user->setResetPasswordToken(hash('sha256', $token));
                $user->setResetPasswordRequestedAt(new \DateTimeImmutable());
                $entityManager->flush();

                $resetUrl = $this->generateUrl(
                    'app_reset_password',
                    ['token' => $token],
                    UrlGeneratorInterface::ABSOLUTE_URL,
                );

                $emailMessage = (new TemplatedEmail())
                    ->from(new Address($this->emailFrom, 'MichaelJPitz.com'))
                    ->to((string) $user->getEmail())
                    ->subject('Réinitialisation de votre mot de passe')
                    ->htmlTemplate('email/reset_password.html.twig')
                    ->context([
                        'user' => $user,
                        'reset_url' => $resetUrl,
                    ]);

                $mailer->send($emailMessage);
            }

            $this->addFlash('success', 'Si un compte existe avec cette adresse email, un lien de réinitialisation vous a été envoyé.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/forgot_password.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/reinitialiser-mot-de-passe/{token}', name: 'app_reset_password')]
    public function resetPassword(
        string $token,
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        // Comparaison par hash : le token reçu en URL est en clair, la BDD stocke le hash
        $user = $userRepository->findOneBy(['resetPasswordToken' => hash('sha256', $token)]);

        if (null === $user) {
            $this->addFlash('error', 'Lien de réinitialisation invalide.');

            return $this->redirectToRoute('app_login');
        }

        $requestedAt = $user->getResetPasswordRequestedAt();

        if (null === $requestedAt || $requestedAt < new \DateTimeImmutable('-1 hour')) {
            $user->setResetPasswordToken(null);
            $user->setResetPasswordRequestedAt(null);
            $entityManager->flush();

            $this->addFlash('error', 'Le lien de réinitialisation a expiré. Veuillez refaire une demande.');

            return $this->redirectToRoute('app_forgot_password');
        }

        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
            $user->setResetPasswordToken(null);
            $user->setResetPasswordRequestedAt(null);
            $entityManager->flush();

            $this->addFlash('success', 'Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/reset_password.html.twig', [
            'form' => $form,
        ]);
    }
}
