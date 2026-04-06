<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\Page;
use App\Repository\CategoryRepository;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\PersistentCollection;

/**
 * Remonte automatiquement la hiérarchie des catégories lors du flush.
 * Si une catégorie enfant est assignée à un Article ou une Page,
 * toutes ses catégories parentes sont ajoutées d'office.
 *
 * Utilise onFlush (avant SQL) pour que les additions ManyToMany
 * soient bien prises en compte par Doctrine, même en cas de mise à jour.
 */
#[AsDoctrineListener(event: Events::onFlush)]
class CategoryHierarchySubscriber
{
    public function __construct(private readonly CategoryRepository $categoryRepository)
    {
    }

    public function onFlush(OnFlushEventArgs $args): void
    {
        $em  = $args->getObjectManager();
        $uow = $em->getUnitOfWork();

        // Index id → Category pour éviter des requêtes répétées
        $indexed = [];
        foreach ($this->categoryRepository->findAll() as $cat) {
            $id = $cat->getId();
            if (null !== $id) {
                $indexed[$id] = $cat;
            }
        }

        $entities = array_merge(
            array_values($uow->getScheduledEntityInsertions()),
            array_values($uow->getScheduledEntityUpdates()),
        );

        foreach ($entities as $entity) {
            if (!$entity instanceof Article && !$entity instanceof Page) {
                continue;
            }

            $added = false;

            foreach ($entity->getCategories()->toArray() as $category) {
                $parentId = $category->getLevel();

                while (null !== $parentId && $parentId > 0 && isset($indexed[$parentId])) {
                    /** @var Category $parent */
                    $parent = $indexed[$parentId];

                    if (!$entity->getCategories()->contains($parent)) {
                        $entity->addCategory($parent);
                        $added = true;
                    }

                    $parentId = $parent->getLevel();
                }
            }

            if ($added) {
                // Recompute le changeset scalaire de l'entité
                $meta = $em->getClassMetadata($entity::class);
                $uow->recomputeSingleEntityChangeSet($meta, $entity);

                // Planifie explicitement la mise à jour de la collection ManyToMany
                $collection = $entity->getCategories();
                if ($collection instanceof PersistentCollection) {
                    $uow->scheduleCollectionUpdate($collection);
                }
            }
        }
    }
}
