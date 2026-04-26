<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Category>
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    /**
     * Retourne toutes les catégories aplaties dans l'ordre hiérarchique,
     * avec la profondeur de chaque nœud.
     *
     * @return list<array{category: Category, depth: int}>
     */
    public function findAllHierarchical(): array
    {
        $all = $this->findBy([], ['title' => 'ASC']);
        $result = [];
        $this->flattenLevel($all, 0, 0, $result);

        return $result;
    }

    /**
     * @param Category[]                                  $all
     * @param list<array{category: Category, depth: int}> $result
     */
    private function flattenLevel(array $all, int $parentId, int $depth, array &$result): void
    {
        foreach ($all as $cat) {
            if (($cat->getLevel() ?? 0) === $parentId) {
                $result[] = ['category' => $cat, 'depth' => $depth];
                if (null !== $cat->getId()) {
                    $this->flattenLevel($all, $cat->getId(), $depth + 1, $result);
                }
            }
        }
    }

    /** @return Category[] */
    public function findWithPublishedArticles(): array
    {
        return $this->createQueryBuilder('c')
            ->innerJoin('c.articles', 'a')
            ->where('a.isPublished = true')
            ->distinct()
            ->getQuery()
            ->getResult();
    }

    /**
     * @return list<array{category: Category, children: list<mixed>}>
     */
    public function findCategoryTree(): array
    {
        $categories = $this->findBy([], ['title' => 'ASC']);

        $indexed = [];
        foreach ($categories as $category) {
            $id = $category->getId();
            if (null !== $id) {
                $indexed[$id] = $category;
            }
        }

        return $this->buildTree($categories, $indexed, 0);
    }

    /**
     * @param Category[]           $categories
     * @param array<int, Category> $indexed
     *
     * @return list<array{category: Category, children: list<mixed>}>
     */
    private function buildTree(array $categories, array $indexed, int $parentId): array
    {
        $tree = [];

        foreach ($categories as $category) {
            if ($category->getLevel() === $parentId) {
                $id = $category->getId();
                if (null !== $id) {
                    $tree[] = [
                        'category' => $category,
                        'children' => $this->buildTree($categories, $indexed, $id),
                    ];
                }
            }
        }

        return $tree;
    }
}
