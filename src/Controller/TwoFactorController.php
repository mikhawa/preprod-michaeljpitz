<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\TwoFactorCodeType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
class TwoFactorController extends AbstractController
{
    private const MAX_ATTEMPTS = 5;

    #[Route('/connexion/code-verification', name: 'app_two_factor', methods: ['GET', 'POST'])]
    public function verify(Request $request, TokenStorageInterface $tokenStorage): Response
    {
        $session = $request->getSession();

        // Si le flag 2FA n'est pas actif, l'utilisateur est déjà pleinement authentifié
        if (!$session->get('2fa_required', false)) {
            return $this->redirectToRoute('app_home');
        }

        // Vérification de l'expiration
        $expiresAt = $session->get('2fa_expires_at', 0);
        if (time() > $expiresAt) {
            $this->invalidateSession($session, $tokenStorage);
            $this->addFlash('error', 'Le code de vérification a expiré. Veuillez vous reconnecter.');

            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(TwoFactorCodeType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $attempts = (int) $session->get('2fa_attempts', 0);

            if ($attempts >= self::MAX_ATTEMPTS) {
                $this->invalidateSession($session, $tokenStorage);
                $this->addFlash('error', 'Trop de tentatives. Veuillez vous reconnecter.');

                return $this->redirectToRoute('app_login');
            }

            /** @var array{code: string} $data */
            $data = $form->getData();
            $inputHash = hash('sha256', $data['code']);
            $storedHash = (string) $session->get('2fa_code_hash', '');

            if (!hash_equals($storedHash, $inputHash)) {
                $session->set('2fa_attempts', $attempts + 1);
                $remaining = self::MAX_ATTEMPTS - $attempts - 1;
                $this->addFlash('error', sprintf(
                    'Code incorrect. %d tentative%s restante%s.',
                    $remaining,
                    $remaining > 1 ? 's' : '',
                    $remaining > 1 ? 's' : '',
                ));

                return $this->render('security/two_factor.html.twig', [
                    'form' => $form,
                ], new Response(status: Response::HTTP_UNPROCESSABLE_ENTITY));
            }

            // Code valide : on efface les données 2FA de session
            $session->remove('2fa_required');
            $session->remove('2fa_code_hash');
            $session->remove('2fa_expires_at');
            $session->remove('2fa_attempts');
            $session->remove('2fa_user_email');

            return $this->redirectToRoute('app_profile');
        }

        return $this->render('security/two_factor.html.twig', [
            'form' => $form,
        ]);
    }

    private function invalidateSession(
        \Symfony\Component\HttpFoundation\Session\SessionInterface $session,
        TokenStorageInterface $tokenStorage,
    ): void {
        $session->remove('2fa_required');
        $session->remove('2fa_code_hash');
        $session->remove('2fa_expires_at');
        $session->remove('2fa_attempts');
        $session->remove('2fa_user_email');
        $tokenStorage->setToken(null);
    }
}
