<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\Article;
use App\Entity\Page;
use App\Repository\CategoryRepository;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

/**
 * Remonte automatiquement la hiérarchie des catégories lors de la sauvegarde
 * d'un article : si une catégorie enfant est sélectionnée, ses catégories
 * parentes sont ajoutées d'office.
 */
#[AsDoctrineListener(event: Events::prePersist)]
#[AsDoctrineListener(event: Events::preUpdate)]
class CategoryHierarchySubscriber
{
    public function __construct(private readonly CategoryRepository $categoryRepository)
    {
    }

    public function prePersist(LifecycleEventArgs $args): void
    {
        $this->resolveParentCategories($args);
    }

    public function preUpdate(LifecycleEventArgs $args): void
    {
        $this->resolveParentCategories($args);
    }

    private function resolveParentCategories(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof Article && !$entity instanceof Page) {
            return;
        }

        // Construit un index id → Category pour éviter des requêtes répétées
        $allCategories = $this->categoryRepository->findAll();
        $indexed = [];
        foreach ($allCategories as $category) {
            $id = $category->getId();
            if (null !== $id) {
                $indexed[$id] = $category;
            }
        }

        // Pour chaque catégorie assignée à l'article, remonte vers la racine
        foreach ($entity->getCategories()->toArray() as $category) {
            $parentId = $category->getLevel();

            while (null !== $parentId && $parentId > 0 && isset($indexed[$parentId])) {
                $parent = $indexed[$parentId];
                $entity->addCategory($parent);
                $parentId = $parent->getLevel();
            }
        }
    }
}
