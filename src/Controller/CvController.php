<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\PageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CvController extends AbstractController
{
    #[Route('/cv', name: 'app_cv')]
    public function index(PageRepository $pageRepository): Response
    {
        $page = $pageRepository->findOneBySlug('cv');

        return $this->render('cv/index.html.twig', [
            'page' => $page,
        ]);
    }
}
