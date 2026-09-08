<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use App\Repository\PageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SitemapController extends AbstractController
{
    #[Route('/sitemap.xml', name: 'app_sitemap', defaults: ['_format' => 'xml'])]
    public function index(
        ArticleRepository $articleRepository,
        CategoryRepository $categoryRepository,
        PageRepository $pageRepository,
    ): Response {
        $articles = $articleRepository->findBy(
            ['isPublished' => true],
            ['publishedAt' => 'DESC'],
        );

        $categories = $categoryRepository->findWithPublishedArticles();

        $pageCv = $pageRepository->findOneBySlug('cv');
        $pageRgpd = $pageRepository->findOneBySlug('rgpd');

        $response = $this->render('sitemap.xml.twig', [
            'articles' => $articles,
            'categories' => $categories,
            'pageCv' => $pageCv,
            'pageRgpd' => $pageRgpd,
        ]);

        $response->headers->set('Content-Type', 'application/xml');

        return $response;
    }
}
