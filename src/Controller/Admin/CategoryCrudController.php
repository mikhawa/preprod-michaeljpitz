<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ColorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/** @extends AbstractCrudController<Category> */
class CategoryCrudController extends AbstractCrudController
{
    public function __construct(private readonly CategoryRepository $categoryRepository)
    {
    }

    public static function getEntityFqcn(): string
    {
        return Category::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Catégorie')
            ->setEntityLabelInPlural('Catégories')
            ->setDefaultSort(['title' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        $parentChoices = ['Aucun parent (racine)' => 0];
        foreach ($this->categoryRepository->findBy([], ['title' => 'ASC']) as $category) {
            $id = $category->getId();
            if (null !== $id) {
                $parentChoices[$category->getTitle() ?? ''] = $id;
            }
        }

        yield IdField::new('id')->hideOnForm();
        yield TextField::new('title', 'Titre');
        yield SlugField::new('slug', 'Identifiant URL')->setTargetFieldName('title');
        yield ColorField::new('color', 'Couleur');
        yield TextareaField::new('description', 'Description')
            ->hideOnIndex();
        yield ChoiceField::new('level', 'Catégorie parente')
            ->setChoices($parentChoices)
            ->renderAsNativeWidget()
            ->hideOnIndex();
    }
}
