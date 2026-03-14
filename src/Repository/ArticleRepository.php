<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Article;
use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Article>
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    public function findPublishedQueryBuilder(?Category $category = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('a')
            ->where('a.isPublished = true')
            ->orderBy('a.publishedAt', 'DESC')
            ->addOrderBy('a.createdAt', 'DESC');

        if (null !== $category) {
            $qb->andWhere(':category MEMBER OF a.categories')
                ->setParameter('category', $category);
        }

        return $qb;
    }

    /** @return Article[] */
    public function findLatestPublished(int $limit = 3): array
    {
        return $this->findPublishedQueryBuilder()
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /** @return Article[] */
    public function findSimilarArticles(Article $article, int $limit = 3): array
    {
        if ($article->getCategories()->isEmpty()) {
            return [];
        }

        return $this->createQueryBuilder('a')
            ->innerJoin('a.categories', 'c')
            ->where('a.isPublished = true')
            ->andWhere('c IN (:categories)')
            ->andWhere('a.id != :id')
            ->setParameter('categories', $article->getCategories())
            ->setParameter('id', $article->getId())
            ->orderBy('a.publishedAt', 'DESC')
            ->groupBy('a.id')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
