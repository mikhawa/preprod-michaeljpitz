<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Repository\CommentRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PublicProfileController extends AbstractController
{
    #[Route('/utilisateur/{userName}', name: 'app_public_profile')]
    public function show(
        #[MapEntity(mapping: ['userName' => 'userName'])] User $user,
        CommentRepository $commentRepository,
    ): Response {
        // Vérifier que l'utilisateur a un compte actif (status = 1)
        if (1 !== $user->getStatus()) {
            throw $this->createNotFoundException('Utilisateur introuvable.');
        }

        // Récupérer les commentaires approuvés de l'utilisateur
        $approvedComments = $commentRepository->findApprovedByUser($user);

        return $this->render('public_profile/show.html.twig', [
            'user' => $user,
            'comments' => $approvedComments,
        ]);
    }
}
