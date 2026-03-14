<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Comment;
use App\Entity\Rating;
use App\Repository\RatingRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ArticleControllerTest extends WebTestCase
{
    use TestDatabaseTrait;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->cleanDatabase();
    }

    public function testArticleIndexIsAccessible(): void
    {
        $this->client->request('GET', '/articles');

        self::assertResponseIsSuccessful();
    }

    public function testArticleIndexFilterByCategory(): void
    {
        $category = $this->createCategory('Symfony', 'symfony-cat');
        $this->createArticle('Article Symfony', 'article-symfony-test', true, $category);

        $this->client->request('GET', '/categorie/symfony-cat');

        self::assertResponseIsSuccessful();
    }

    public function testArticleIndexWithInvalidCategory(): void
    {
        $this->client->request('GET', '/categorie/categorie-inexistante');

        self::assertResponseStatusCodeSame(404);
    }

    public function testArticleShowPublished(): void
    {
        $this->createArticle('Article publie', 'article-publie-test', true);

        $this->client->request('GET', '/article/article-publie-test');

        self::assertResponseIsSuccessful();
    }

    public function testArticleShowUnpublished(): void
    {
        $this->createArticle('Article brouillon', 'article-brouillon-test', false);

        $this->client->request('GET', '/article/article-brouillon-test');

        self::assertResponseStatusCodeSame(404);
    }

    public function testCommentSubmissionAsAuthenticatedUser(): void
    {
        $user = $this->createActiveUser();
        $article = $this->createArticle();

        $this->client->loginUser($user);
        $crawler = $this->client->request('GET', '/article/'.$article->getSlug());

        $form = $crawler->filter('form[name="comment"]')->form([
            'comment[content]' => 'Ceci est un commentaire de test.',
        ]);
        $this->client->submit($form);

        self::assertResponseRedirects('/article/'.$article->getSlug());

        $em = $this->getEntityManager();
        $comments = $em->getRepository(Comment::class)->findBy(['article' => $article]);
        self::assertCount(1, $comments);
        self::assertFalse($comments[0]->isApproved());
    }

    public function testCommentFormNotShownForAnonymous(): void
    {
        $article = $this->createArticle();

        $crawler = $this->client->request('GET', '/article/'.$article->getSlug());

        self::assertResponseIsSuccessful();
        self::assertSame(0, $crawler->filter('form[name="comment"]')->count());
    }

    public function testRatingAsAuthenticatedUser(): void
    {
        $user = $this->createActiveUser();
        $article = $this->createArticle();

        $this->client->loginUser($user);
        $crawler = $this->client->request('GET', '/article/'.$article->getSlug());
        $csrfToken = $crawler->filter('form[action*="/rate"] input[name="_token"]')->attr('value');

        $this->client->request('POST', '/article/'.$article->getSlug().'/rate', [
            'rating' => '4',
            '_token' => $csrfToken,
        ]);

        self::assertResponseRedirects('/article/'.$article->getSlug());

        /** @var RatingRepository $ratingRepo */
        $ratingRepo = static::getContainer()->get(RatingRepository::class);
        $rating = $ratingRepo->findByUserAndArticle($user, $article);

        self::assertNotNull($rating);
        self::assertSame(4, $rating->getRating());
    }

    public function testRatingWithInvalidValue(): void
    {
        $user = $this->createActiveUser();
        $article = $this->createArticle();

        $this->client->loginUser($user);
        $crawler = $this->client->request('GET', '/article/'.$article->getSlug());
        $csrfToken = $crawler->filter('form[action*="/rate"] input[name="_token"]')->attr('value');

        $this->client->request('POST', '/article/'.$article->getSlug().'/rate', [
            'rating' => '0',
            '_token' => $csrfToken,
        ]);

        self::assertResponseRedirects('/article/'.$article->getSlug());
        $this->client->followRedirect();
        self::assertSelectorExists('.bg-red-100');
    }

    public function testRatingWithInvalidCsrf(): void
    {
        $user = $this->createActiveUser();
        $article = $this->createArticle();

        $this->client->loginUser($user);
        $this->client->request('POST', '/article/'.$article->getSlug().'/rate', [
            'rating' => '3',
            '_token' => 'mauvais-csrf-token',
        ]);

        self::assertResponseRedirects('/article/'.$article->getSlug());
        $this->client->followRedirect();
        self::assertSelectorExists('.bg-red-100');
    }

    public function testRatingRequiresAuthentication(): void
    {
        $article = $this->createArticle();

        $this->client->request('POST', '/article/'.$article->getSlug().'/rate', [
            'rating' => '3',
            '_token' => 'token',
        ]);

        self::assertResponseRedirects();
        self::assertStringContainsString('connexion', (string) $this->client->getResponse()->headers->get('Location'));
    }

    public function testRatingUpdateExisting(): void
    {
        $user = $this->createActiveUser();
        $article = $this->createArticle();

        $em = $this->getEntityManager();
        $rating = new Rating();
        $rating->setUser($user);
        $rating->setArticle($article);
        $rating->setRating(2);
        $em->persist($rating);
        $em->flush();

        $this->client->loginUser($user);
        $crawler = $this->client->request('GET', '/article/'.$article->getSlug());
        $csrfToken = $crawler->filter('form[action*="/rate"] input[name="_token"]')->attr('value');

        $this->client->request('POST', '/article/'.$article->getSlug().'/rate', [
            'rating' => '5',
            '_token' => $csrfToken,
        ]);

        self::assertResponseRedirects('/article/'.$article->getSlug());

        $em->clear();

        /** @var RatingRepository $ratingRepo */
        $ratingRepo = static::getContainer()->get(RatingRepository::class);
        $updatedRating = $ratingRepo->findByUserAndArticle($user, $article);

        self::assertNotNull($updatedRating);
        self::assertSame(5, $updatedRating->getRating());

        $allRatings = $em->getRepository(Rating::class)->findBy([
            'user' => $user,
            'article' => $article,
        ]);
        self::assertCount(1, $allRatings);
    }
}
