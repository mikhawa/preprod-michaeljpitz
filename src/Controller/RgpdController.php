<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\PageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RgpdController extends AbstractController
{
    #[Route('/rgpd', name: 'app_rgpd')]
    public function index(PageRepository $pageRepository): Response
    {
        $page = $pageRepository->findOneBySlug('rgpd');

        return $this->render('rgpd/index.html.twig', [
            'page' => $page,
        ]);
    }
}
