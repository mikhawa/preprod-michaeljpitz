<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\Article;
use App\Entity\Page;
use App\Repository\CategoryRepository;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\PersistentCollection;

/**
 * Remonte automatiquement la hiérarchie des catégories avant le flush.
 * Si une catégorie enfant est assignée à un Article ou une Page,
 * toutes ses catégories parentes sont ajoutées d'office.
 *
 * Utilise preFlush (avant computeChangeSets) pour que Doctrine détecte
 * les additions ManyToMany naturellement, sans manipulation du UnitOfWork.
 */
#[AsDoctrineListener(event: Events::preFlush)]
class CategoryHierarchySubscriber
{
    public function __construct(private readonly CategoryRepository $categoryRepository)
    {
    }

    public function preFlush(PreFlushEventArgs $args): void
    {
        $em = $args->getObjectManager();
        $uow = $em->getUnitOfWork();

        // Collecte les entités à traiter : insertions en attente + entités gérées
        $entities = array_values($uow->getScheduledEntityInsertions());

        foreach ($uow->getIdentityMap() as $managedEntities) {
            foreach ($managedEntities as $managed) {
                if (!$managed instanceof Article && !$managed instanceof Page) {
                    continue;
                }
                // Ignorer les entités dont la collection de catégories n'a pas été chargée
                $collection = $managed->getCategories();
                if ($collection instanceof PersistentCollection && !$collection->isInitialized()) {
                    continue;
                }
                $entities[] = $managed;
            }
        }

        if ([] === $entities) {
            return;
        }

        // Index id → Category pour éviter des requêtes répétées
        $indexed = [];
        foreach ($this->categoryRepository->findAll() as $cat) {
            $id = $cat->getId();
            if (null !== $id) {
                $indexed[$id] = $cat;
            }
        }

        foreach ($entities as $entity) {
            if (!$entity instanceof Article && !$entity instanceof Page) {
                continue;
            }

            foreach ($entity->getCategories()->toArray() as $category) {
                $parentId = $category->getLevel();

                while (null !== $parentId && $parentId > 0 && isset($indexed[$parentId])) {
                    $parent = $indexed[$parentId];

                    if (!$entity->getCategories()->contains($parent)) {
                        $entity->addCategory($parent);
                    }

                    $parentId = $parent->getLevel();
                }
            }
        }
    }
}
