<?php

declare(strict_types=1);

namespace App\Twig\Components;

use App\Repository\CategoryRepository;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('Navbar')]
class NavbarComponent
{
    public function __construct(
        private readonly CategoryRepository $categoryRepository,
    ) {
    }

    /**
     * @return list<array{category: \App\Entity\Category, children: list<mixed>}>
     */
    public function getCategoryTree(): array
    {
        return $this->categoryRepository->findCategoryTree();
    }
}
