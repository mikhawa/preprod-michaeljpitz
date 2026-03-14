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
