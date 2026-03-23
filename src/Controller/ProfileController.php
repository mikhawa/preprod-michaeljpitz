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

class ProfileController extends AbstractController
{
    public function __construct(
        private readonly TurnstileValidator $turnstileValidator,
        #[Autowire('%env(TURNSTILE_SITE_KEY)%')]
        private readonly string $turnstileSiteKey,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
    }

    #[Route('/profil', name: 'app_profile', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        CommentRepository $commentRepository,
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
                $uploadDir = $this->projectDir.'/public/uploads/avatars';
                $this->processAvatarUpload($user, $croppedData, $uploadDir);
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

    private function processAvatarUpload(User $user, string $base64Data, string $uploadDir): void
    {
        // Whitelist stricte des MIME types autorisés
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $mimeToExtension = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

        if (!preg_match('/^data:(image\/(?:jpeg|png|webp));base64,/', $base64Data)) {
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

        // Double vérification : MIME réel du contenu binaire
        $finfo = new \finfo(\FILEINFO_MIME_TYPE);
        $realMime = $finfo->buffer($decodedData);

        if (!in_array($realMime, $allowedMimeTypes, true)) {
            return;
        }

        $extension = $mimeToExtension[$realMime];

        // Crée le répertoire si nécessaire (avec permissions 0775)
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        // Supprime l'ancien avatar s'il existe
        $oldAvatarName = $user->getAvatarName();
        if (null !== $oldAvatarName) {
            $oldPath = $uploadDir.'/'.$oldAvatarName;
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
        }

        // Écrit directement le fichier dans le dossier d'upload (évite le rename /tmp → destination)
        $newFilename = bin2hex(random_bytes(16)).'.'.$extension;
        $written = file_put_contents($uploadDir.'/'.$newFilename, $decodedData);

        if (false === $written) {
            return;
        }

        $user->setAvatarName($newFilename);
        $user->setUpdatedAt(new \DateTimeImmutable());
    }
}
