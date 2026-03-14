<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Article;
use App\Entity\Rating;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Rating>
 */
class RatingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Rating::class);
    }

    public function findByUserAndArticle(User $user, Article $article): ?Rating
    {
        return $this->findOneBy(['user' => $user, 'article' => $article]);
    }

    public function getAverageRatingForArticle(Article $article): ?float
    {
        $result = $this->createQueryBuilder('r')
            ->select('AVG(r.rating) as avgRating')
            ->andWhere('r.article = :article')
            ->setParameter('article', $article)
            ->getQuery()
            ->getSingleScalarResult();

        return null !== $result ? round((float) $result, 1) : null;
    }
}
