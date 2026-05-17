<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use App\Repository\CommentRepository;
use App\Repository\PageRepository;
use App\Repository\RatingRepository;
use App\Repository\UserRepository;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private readonly ArticleRepository $articleRepository,
        private readonly CategoryRepository $categoryRepository,
        private readonly UserRepository $userRepository,
        private readonly CommentRepository $commentRepository,
        private readonly RatingRepository $ratingRepository,
        private readonly PageRepository $pageRepository,
    ) {}

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        $gen = $this->container->get(AdminUrlGenerator::class);

        return $this->render('admin/dashboard.html.twig', [
            'cards' => [
                [
                    'label'     => 'Articles',
                    'icon'      => 'fa fa-newspaper',
                    'url'       => $gen->setController(ArticleCrudController::class)->generateUrl(),
                    'count'     => $this->articleRepository->count([]),
                    'color_key' => 'articles',
                ],
                [
                    'label'     => 'Catégories',
                    'icon'      => 'fa fa-tags',
                    'url'       => $gen->setController(CategoryCrudController::class)->generateUrl(),
                    'count'     => $this->categoryRepository->count([]),
                    'color_key' => 'categories',
                ],
                [
                    'label'     => 'Menu des catégories',
                    'icon'      => 'fa fa-sitemap',
                    'url'       => $this->generateUrl('admin_category_tree'),
                    'count'     => null,
                    'color_key' => 'tree',
                ],
                [
                    'label'     => 'Utilisateurs',
                    'icon'      => 'fa fa-users',
                    'url'       => $gen->setController(UserCrudController::class)->generateUrl(),
                    'count'     => $this->userRepository->count([]),
                    'color_key' => 'users',
                ],
                [
                    'label'     => 'Commentaires',
                    'icon'      => 'fa fa-comments',
                    'url'       => $gen->setController(CommentCrudController::class)->generateUrl(),
                    'count'     => $this->commentRepository->count([]),
                    'color_key' => 'comments',
                ],
                [
                    'label'     => 'Notes',
                    'icon'      => 'fa fa-star',
                    'url'       => $gen->setController(RatingCrudController::class)->generateUrl(),
                    'count'     => $this->ratingRepository->count([]),
                    'color_key' => 'ratings',
                ],
                [
                    'label'     => 'Pages',
                    'icon'      => 'fa fa-file-alt',
                    'url'       => $gen->setController(PageCrudController::class)->generateUrl(),
                    'count'     => $this->pageRepository->count([]),
                    'color_key' => 'pages',
                ],
                [
                    'label'     => 'Retour au site',
                    'icon'      => 'fa fa-arrow-left',
                    'url'       => $this->generateUrl('app_home'),
                    'count'     => null,
                    'color_key' => 'back',
                ],
            ],
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Portfolio - Administration');
    }

    public function configureAssets(): Assets
    {
        return parent::configureAssets()
            ->addAssetMapperEntry('app')
            ->addHtmlContentToHead('<meta name="turbo-visit-control" content="reload">')
            ->addHtmlContentToHead('<meta name="turbo-root" content="/DO_NOT_INTERCEPT">');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Tableau de bord', 'fa fa-home');
        yield MenuItem::linkTo(ArticleCrudController::class, 'Articles', 'fa fa-newspaper');
        yield MenuItem::linkTo(CategoryCrudController::class, 'Catégories', 'fa fa-tags');
        yield MenuItem::linkToRoute('Menu des catégories', 'fa fa-sitemap', 'admin_category_tree');
        yield MenuItem::linkTo(UserCrudController::class, 'Utilisateurs', 'fa fa-users');
        yield MenuItem::linkTo(CommentCrudController::class, 'Commentaires', 'fa fa-comments');
        yield MenuItem::linkTo(RatingCrudController::class, 'Notes', 'fa fa-star');
        yield MenuItem::linkTo(PageCrudController::class, 'Pages', 'fa fa-file-alt');
        yield MenuItem::linkToRoute('Retour au site', 'fa fa-arrow-left', 'app_home');
    }
}
