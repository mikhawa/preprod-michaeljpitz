<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileType;
use App\Repository\CommentRepository;
use App\Service\TurnstileValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

class ProfileController extends AbstractController
{
    public function __construct(
        private readonly string $avatarDirectory,
        private readonly TurnstileValidator $turnstileValidator,
        #[Autowire('%env(TURNSTILE_SITE_KEY)%')]
        private readonly string $turnstileSiteKey,
    ) {
    }

    #[Route('/profil', name: 'app_profile', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        CommentRepository $commentRepository,
        SluggerInterface $slugger,
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $turnstileEnabled = !empty($this->turnstileSiteKey)
                && 'YOUR_TURNSTILE_SITE_KEY' !== $this->turnstileSiteKey
                && !str_starts_with($this->turnstileSiteKey, '1x00000000000000000000');

            if ($turnstileEnabled) {
                $turnstileToken = $request->request->getString('cf-turnstile-response');

                if (!$this->turnstileValidator->validate($turnstileToken, $request->getClientIp())) {
                    $this->addFlash('error', 'La vérification anti-robot a échoué. Veuillez réessayer.');

                    $comments = $commentRepository->findByUser($user);

                    return $this->render('profile/index.html.twig', [
                        'form' => $form,
                        'user' => $user,
                        'comments' => $comments,
                        'turnstileSiteKey' => $this->turnstileSiteKey,
                    ], new Response(status: Response::HTTP_UNPROCESSABLE_ENTITY));
                }
            }

            $croppedData = $form->get('croppedAvatarData')->getData();

            if (null !== $croppedData && '' !== $croppedData) {
                $this->processAvatarUpload($user, $croppedData);
            }

            $entityManager->flush();
            $this->addFlash('success', 'Votre profil a été mis à jour avec succès.');

            return $this->redirectToRoute('app_profile');
        }

        $comments = $commentRepository->findByUser($user);

        return $this->render('profile/index.html.twig', [
            'form' => $form,
            'user' => $user,
            'comments' => $comments,
            'turnstileSiteKey' => $this->turnstileSiteKey,
        ]);
    }

    private function processAvatarUpload(User $user, string $base64Data): void
    {
        if (!str_starts_with($base64Data, 'data:image/')) {
            return;
        }

        $matches = [];
        if (!preg_match('/^data:image\/(\w+);base64,/', $base64Data, $matches)) {
            return;
        }

        $extension = $matches[1];
        if (!in_array($extension, ['jpeg', 'png', 'webp'], true)) {
            return;
        }

        $data = substr($base64Data, strpos($base64Data, ',') + 1);
        $decodedData = base64_decode($data, true);

        if (false === $decodedData) {
            return;
        }

        if (strlen($decodedData) > 10 * 1024 * 1024) {
            return;
        }

        $oldAvatarName = $user->getAvatarName();
        if (null !== $oldAvatarName) {
            $oldAvatarPath = $this->avatarDirectory.'/'.$oldAvatarName;
            if (file_exists($oldAvatarPath)) {
                unlink($oldAvatarPath);
            }
        }

        if (!is_dir($this->avatarDirectory)) {
            mkdir($this->avatarDirectory, 0775, true);
        }

        $fileName = uniqid('avatar_', true).'.'.('jpeg' === $extension ? 'jpg' : $extension);
        $filePath = $this->avatarDirectory.'/'.$fileName;

        file_put_contents($filePath, $decodedData);

        $user->setAvatarName($fileName);
        $user->setUpdatedAt(new \DateTimeImmutable());
    }
}
