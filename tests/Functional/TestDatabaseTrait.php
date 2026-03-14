<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Rating;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

trait TestDatabaseTrait
{
    private function getEntityManager(): EntityManagerInterface
    {
        return static::getContainer()->get(EntityManagerInterface::class);
    }

    private function getPasswordHasher(): UserPasswordHasherInterface
    {
        return static::getContainer()->get(UserPasswordHasherInterface::class);
    }

    private function createUser(
        string $email = 'test@example.com',
        string $password = 'password123',
        string $userName = 'testuser',
        int $status = 1,
        array $roles = [],
        ?string $activationToken = null,
        ?string $resetPasswordToken = null,
        ?\DateTimeImmutable $resetPasswordRequestedAt = null,
        ?\DateTimeImmutable $createdAt = null,
    ): User {
        $em = $this->getEntityManager();
        $hasher = $this->getPasswordHasher();

        $user = new User();
        $user->setEmail($email);
        $user->setUserName($userName);
        $user->setPassword($hasher->hashPassword($user, $password));
        $user->setStatus($status);
        $user->setRoles($roles);
        $user->setActivationToken($activationToken);
        $user->setResetPasswordToken($resetPasswordToken);
        $user->setResetPasswordRequestedAt($resetPasswordRequestedAt);

        $em->persist($user);
        $em->flush();

        // Modifier createdAt après le persist (le lifecycle callback le définit automatiquement)
        if (null !== $createdAt) {
            $em->getConnection()->executeStatement(
                'UPDATE `user` SET created_at = :createdAt WHERE id = :id',
                ['createdAt' => $createdAt->format('Y-m-d H:i:s'), 'id' => $user->getId()],
            );
            $em->refresh($user);
        }

        return $user;
    }

    private function createActiveUser(
        string $email = 'active@example.com',
        string $password = 'password123',
        string $userName = 'activeuser',
    ): User {
        return $this->createUser($email, $password, $userName, 1);
    }

    private function createInactiveUser(
        string $email = 'inactive@example.com',
        string $password = 'password123',
        string $userName = 'inactiveuser',
        ?string $activationToken = 'valid-activation-token',
    ): User {
        return $this->createUser($email, $password, $userName, 0, [], $activationToken);
    }

    private function createBannedUser(
        string $email = 'banned@example.com',
        string $password = 'password123',
        string $userName = 'banneduser',
    ): User {
        return $this->createUser($email, $password, $userName, 2);
    }

    private function createAdminUser(
        string $email = 'admin@example.com',
        string $password = 'password123',
        string $userName = 'adminuser',
    ): User {
        return $this->createUser($email, $password, $userName, 1, ['ROLE_ADMIN']);
    }

    private function createCategory(
        string $title = 'PHP',
        string $slug = 'php-category',
        ?string $color = '#3498db',
    ): Category {
        $em = $this->getEntityManager();

        $category = new Category();
        $category->setTitle($title);
        $category->setSlug($slug);
        $category->setColor($color);

        $em->persist($category);
        $em->flush();

        return $category;
    }

    private function createArticle(
        string $title = 'Article de test',
        string $slug = 'article-de-test-slug',
        bool $isPublished = true,
        ?Category $category = null,
    ): Article {
        $em = $this->getEntityManager();

        $article = new Article();
        $article->setTitle($title);
        $article->setSlug($slug);
        $article->setContent('Contenu de test suffisamment long pour passer la validation minimale de vingt caractères.');
        $article->setIsPublished($isPublished);
        if (null !== $category) {
            $article->addCategory($category);
        }

        if ($isPublished) {
            $article->setPublishedAt(new \DateTimeImmutable());
        }

        $em->persist($article);
        $em->flush();

        return $article;
    }

    private function createComment(
        User $user,
        Article $article,
        string $content = 'Commentaire de test suffisant.',
        bool $isApproved = false,
    ): Comment {
        $em = $this->getEntityManager();

        $comment = new Comment();
        $comment->setUser($user);
        $comment->setArticle($article);
        $comment->setContent($content);
        $comment->setIsApproved($isApproved);

        $em->persist($comment);
        $em->flush();

        return $comment;
    }

    private function createRating(
        User $user,
        Article $article,
        int $rating = 4,
    ): Rating {
        $em = $this->getEntityManager();

        $ratingEntity = new Rating();
        $ratingEntity->setUser($user);
        $ratingEntity->setArticle($article);
        $ratingEntity->setRating($rating);

        $em->persist($ratingEntity);
        $em->flush();

        return $ratingEntity;
    }

    private function cleanDatabase(): void
    {
        $em = $this->getEntityManager();
        $connection = $em->getConnection();

        $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 0');
        $connection->executeStatement('TRUNCATE TABLE rating');
        $connection->executeStatement('TRUNCATE TABLE comment');
        $connection->executeStatement('TRUNCATE TABLE article_category');
        $connection->executeStatement('TRUNCATE TABLE article');
        $connection->executeStatement('TRUNCATE TABLE category');
        $connection->executeStatement('TRUNCATE TABLE `user`');
        $connection->executeStatement('TRUNCATE TABLE page');
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
