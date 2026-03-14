<?php

declare(strict_types=1);

namespace App\Tests\Functional\Repository;

use App\Repository\CategoryRepository;
use App\Tests\Functional\TestDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CategoryRepositoryTest extends KernelTestCase
{
    use TestDatabaseTrait;

    private CategoryRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->cleanDatabase();
        $this->repository = static::getContainer()->get(CategoryRepository::class);
    }

    public function testFindCategoryTreeRootCategories(): void
    {
        $em = $this->getEntityManager();

        $cat1 = $this->createCategory('PHP', 'php-tree');
        $cat1->setLevel(0);
        $em->flush();

        $cat2 = $this->createCategory('JavaScript', 'js-tree');
        $cat2->setLevel(0);
        $em->flush();

        $tree = $this->repository->findCategoryTree();

        self::assertCount(2, $tree);
    }

    public function testFindCategoryTreeHierarchy(): void
    {
        $em = $this->getEntityManager();

        $parent = $this->createCategory('Backend', 'backend-tree');
        $parent->setLevel(0);
        $em->flush();

        $child = $this->createCategory('PHP', 'php-child-tree');
        $child->setLevel($parent->getId());
        $em->flush();

        $tree = $this->repository->findCategoryTree();

        self::assertCount(1, $tree);
        self::assertSame('Backend', $tree[0]['category']->getTitle());
        self::assertCount(1, $tree[0]['children']);
        self::assertSame('PHP', $tree[0]['children'][0]['category']->getTitle());
    }

    public function testFindCategoryTreeEmpty(): void
    {
        $tree = $this->repository->findCategoryTree();

        self::assertCount(0, $tree);
    }

    public function testFindCategoryTreeAlphabeticalOrder(): void
    {
        $em = $this->getEntityManager();

        $this->createCategory('Symfony', 'symfony-alpha');
        $em->createQuery('UPDATE App\Entity\Category c SET c.level = 0 WHERE c.slug = :slug')
            ->setParameter('slug', 'symfony-alpha')
            ->execute();

        $this->createCategory('Docker', 'docker-alpha');
        $em->createQuery('UPDATE App\Entity\Category c SET c.level = 0 WHERE c.slug = :slug')
            ->setParameter('slug', 'docker-alpha')
            ->execute();

        $em->clear();
        $tree = $this->repository->findCategoryTree();

        self::assertCount(2, $tree);
        self::assertSame('Docker', $tree[0]['category']->getTitle());
        self::assertSame('Symfony', $tree[1]['category']->getTitle());
    }
}
