<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Rating;
use App\Entity\User;
use App\Form\CommentType;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use App\Repository\CommentRepository;
use App\Repository\RatingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ArticleController extends AbstractController
{
    public function __construct(
        #[Autowire('%env(EMAIL_NOTIFICATIONS_FROM)%')]
        private readonly string $emailFrom,
        #[Autowire('%env(ADMIN_EMAIL)%')]
        private readonly string $adminEmail,
    ) {
    }

    #[Route('/articles', name: 'app_article_index')]
    public function index(
        Request $request,
        ArticleRepository $articleRepository,
        CategoryRepository $categoryRepository,
        PaginatorInterface $paginator,
    ): Response {
        $queryBuilder = $articleRepository->findPublishedQueryBuilder();

        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            9,
        );

        $categories = $categoryRepository->findBy([], ['title' => 'ASC']);

        return $this->render('article/index.html.twig', [
            'pagination' => $pagination,
            'categories' => $categories,
            'currentCategory' => null,
        ]);
    }

    #[Route('/categorie/{slug}', name: 'app_category_show')]
    public function category(
        #[MapEntity(mapping: ['slug' => 'slug'])] Category $category,
        Request $request,
        ArticleRepository $articleRepository,
        CategoryRepository $categoryRepository,
        PaginatorInterface $paginator,
    ): Response {
        $queryBuilder = $articleRepository->findPublishedQueryBuilder($category);

        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            9,
        );

        $categories = $categoryRepository->findBy([], ['title' => 'ASC']);

        return $this->render('article/index.html.twig', [
            'pagination' => $pagination,
            'categories' => $categories,
            'currentCategory' => $category,
        ]);
    }

    #[Route('/article/{slug}', name: 'app_article_show')]
    public function show(
        #[MapEntity(mapping: ['slug' => 'slug'])] Article $article,
        ArticleRepository $articleRepository,
        CommentRepository $commentRepository,
        RatingRepository $ratingRepository,
        Request $request,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
    ): Response {
        if (!$article->isPublished()) {
            throw $this->createNotFoundException('Article introuvable.');
        }

        $similarArticles = $articleRepository->findSimilarArticles($article);
        $comments = $commentRepository->findByArticle($article);
        $averageRating = $ratingRepository->getAverageRatingForArticle($article);
        $ratingCount = count($article->getRatings());

        $commentForm = null;
        $userRating = null;

        /** @var User|null $user */
        $user = $this->getUser();

        if (null !== $user) {
            $comment = new Comment();
            $commentForm = $this->createForm(CommentType::class, $comment);
            $commentForm->handleRequest($request);

            if ($commentForm->isSubmitted() && $commentForm->isValid()) {
                $comment->setUser($user);
                $comment->setArticle($article);

                // Les commentaires des administrateurs sont automatiquement approuvés
                if ($this->isGranted('ROLE_ADMIN')) {
                    $comment->setIsApproved(true);
                }

                $entityManager->persist($comment);
                $entityManager->flush();

                $articleUrl = $this->generateUrl(
                    'app_article_show',
                    ['slug' => $article->getSlug()],
                    UrlGeneratorInterface::ABSOLUTE_URL,
                );

                $adminNotification = (new TemplatedEmail())
                    ->from(new Address($this->emailFrom, 'CV Mikhawa'))
                    ->to($this->adminEmail)
                    ->subject('Nouveau commentaire - '.$article->getTitle())
                    ->htmlTemplate('email/new_comment_notification.html.twig')
                    ->context([
                        'userName' => $user->getUserName(),
                        'userEmail' => $user->getEmail(),
                        'articleTitle' => $article->getTitle(),
                        'articleUrl' => $articleUrl,
                        'commentContent' => $comment->getContent(),
                        'commentDate' => new \DateTimeImmutable(),
                    ]);

                $mailer->send($adminNotification);

                if ($this->isGranted('ROLE_ADMIN')) {
                    $this->addFlash('success', 'Votre commentaire a été publié.');
                } else {
                    $this->addFlash('success', 'Votre commentaire a été soumis et sera visible après validation par un administrateur.');
                }

                return $this->redirectToRoute('app_article_show', ['slug' => $article->getSlug()]);
            }

            $userRating = $ratingRepository->findByUserAndArticle($user, $article);
        }

        return $this->render('article/show.html.twig', [
            'article' => $article,
            'similarArticles' => $similarArticles,
            'comments' => $comments,
            'commentForm' => $commentForm,
            'averageRating' => $averageRating,
            'ratingCount' => $ratingCount,
            'userRating' => $userRating,
        ]);
    }

    #[Route('/article/{slug}/rate', name: 'app_article_rate', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function rate(
        #[MapEntity(mapping: ['slug' => 'slug'])] Article $article,
        Request $request,
        RatingRepository $ratingRepository,
        EntityManagerInterface $entityManager,
    ): Response {
        if (!$article->isPublished()) {
            throw $this->createNotFoundException('Article introuvable.');
        }

        if (!$this->isCsrfTokenValid('rate-article', $request->request->getString('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');

            return $this->redirectToRoute('app_article_show', ['slug' => $article->getSlug()]);
        }

        /** @var User $user */
        $user = $this->getUser();
        $ratingValue = $request->request->getInt('rating');

        if ($ratingValue < 1 || $ratingValue > 5) {
            $this->addFlash('error', 'La note doit être comprise entre 1 et 5.');

            return $this->redirectToRoute('app_article_show', ['slug' => $article->getSlug()]);
        }

        $rating = $ratingRepository->findByUserAndArticle($user, $article);

        if (null === $rating) {
            $rating = new Rating();
            $rating->setUser($user);
            $rating->setArticle($article);
            $entityManager->persist($rating);
        }

        $rating->setRating($ratingValue);
        $entityManager->flush();

        $this->addFlash('success', 'Votre note a été enregistrée.');

        return $this->redirectToRoute('app_article_show', ['slug' => $article->getSlug()]);
    }
}
