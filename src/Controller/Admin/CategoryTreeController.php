<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class CategoryTreeController extends AbstractController
{
    public function __construct(
        private readonly CategoryRepository $categoryRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly AdminUrlGenerator $adminUrlGenerator,
    ) {
    }

    #[Route('/admin/category-tree', name: 'admin_category_tree', methods: ['GET'])]
    public function index(): Response
    {
        $tree = $this->categoryRepository->findCategoryTree();

        $editUrls = [];
        foreach ($this->categoryRepository->findAll() as $cat) {
            $id = $cat->getId();
            if (null !== $id) {
                $editUrls[$id] = $this->adminUrlGenerator
                    ->setController(CategoryCrudController::class)
                    ->setAction(Action::EDIT)
                    ->setEntityId($id)
                    ->generateUrl();
            }
        }

        $adminUrl = $this->adminUrlGenerator
            ->setController(CategoryCrudController::class)
            ->generateUrl();

        $newUrl = $this->adminUrlGenerator
            ->setController(CategoryCrudController::class)
            ->setAction(Action::NEW)
            ->generateUrl();

        return $this->render('admin/category_tree.html.twig', [
            'categoryTree' => $tree,
            'editUrls' => $editUrls,
            'adminUrl' => $adminUrl,
            'newUrl' => $newUrl,
        ]);
    }

    #[Route('/admin/category-tree/save', name: 'admin_category_tree_save', methods: ['POST'])]
    public function save(Request $request): JsonResponse
    {
        if ('XMLHttpRequest' !== $request->headers->get('X-Requested-With')) {
            return new JsonResponse(['error' => 'Requête non autorisée.'], Response::HTTP_FORBIDDEN);
        }

        if (!$this->isCsrfTokenValid('category_tree_save', $request->headers->get('X-CSRF-Token'))) {
            return new JsonResponse(['error' => 'Token CSRF invalide.'], Response::HTTP_FORBIDDEN);
        }

        $raw = json_decode($request->getContent(), true);

        if (!is_array($raw)) {
            return new JsonResponse(['error' => 'Données invalides.'], Response::HTTP_BAD_REQUEST);
        }

        /** @var array<int, int> $hierarchyMap id => parentId de la nouvelle hiérarchie */
        $hierarchyMap = [];
        foreach ($raw as $item) {
            if (!isset($item['id'], $item['parentId'])) {
                continue;
            }
            $hierarchyMap[(int) $item['id']] = (int) $item['parentId'];
        }

        // Charger toutes les catégories en une seule requête
        $all = [];
        foreach ($this->categoryRepository->findAll() as $cat) {
            $id = $cat->getId();
            if (null !== $id) {
                $all[$id] = $cat;
            }
        }

        foreach ($hierarchyMap as $id => $parentId) {
            if (!isset($all[$id])) {
                continue;
            }

            // Vérifier que le parent existe (sauf 0 = racine)
            if (0 !== $parentId && !isset($all[$parentId])) {
                return new JsonResponse(
                    ['error' => sprintf('Catégorie parente #%d introuvable.', $parentId)],
                    Response::HTTP_BAD_REQUEST,
                );
            }

            // Détecter les dépendances circulaires
            if ($this->hasCircularDependency($id, $parentId, $hierarchyMap)) {
                return new JsonResponse(
                    ['error' => sprintf('Dépendance circulaire détectée pour la catégorie #%d.', $id)],
                    Response::HTTP_BAD_REQUEST,
                );
            }

            $all[$id]->setLevel($parentId);
        }

        $this->entityManager->flush();

        return new JsonResponse(['success' => true]);
    }

    /** @param array<int, int> $hierarchyMap */
    private function hasCircularDependency(int $categoryId, int $parentId, array $hierarchyMap): bool
    {
        $visited = [$categoryId];
        $current = $parentId;

        while (0 !== $current) {
            if (in_array($current, $visited, true)) {
                return true;
            }
            $visited[] = $current;
            $current = $hierarchyMap[$current] ?? 0;
        }

        return false;
    }
}
