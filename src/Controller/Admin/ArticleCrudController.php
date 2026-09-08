<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Article;
use App\Entity\Category;
use App\Repository\CategoryRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/** @extends AbstractCrudController<Article> */
class ArticleCrudController extends AbstractCrudController
{
    public function __construct(private readonly CategoryRepository $categoryRepository)
    {
    }

    public static function getEntityFqcn(): string
    {
        return Article::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Article')
            ->setEntityLabelInPlural('Articles')
            ->setDefaultSort(['id' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('title', 'Titre');
        yield SlugField::new('slug', 'Identifiant URL')->setTargetFieldName('title');
        yield TextareaField::new('content', 'Contenu')
            ->hideOnIndex()
            ->renderAsHtml()
            ->setFormTypeOption('row_attr', [
                'data-controller' => 'suneditor',
            ]);
        yield TextareaField::new('excerpt', 'Extrait')
            ->hideOnIndex();
        yield ImageField::new('featuredImage', 'Image')
            ->setBasePath('/uploads/articles')
            ->setUploadDir('public/uploads/articles')
            ->setUploadedFileNamePattern('[randomhash].[extension]')
            ->setRequired(false);
        // Catégories triées hiérarchiquement avec indentation visuelle
        $hierarchical = $this->categoryRepository->findAllHierarchical();
        $orderedEntities = array_column($hierarchical, 'category');
        $labelMap = [];
        foreach ($hierarchical as ['category' => $cat, 'depth' => $depth]) {
            /* @var Category $cat */
            $labelMap[$cat->getId()] = str_repeat('— ', $depth).$cat->getTitle();
        }

        yield AssociationField::new('categories', 'Catégories')
            ->setFormTypeOption('by_reference', false)
            ->setFormTypeOption('expanded', true)
            ->setFormTypeOption('multiple', true)
            ->setFormTypeOption('choices', $orderedEntities)
            ->setFormTypeOption('choice_label', static function (Category $cat) use ($labelMap): string {
                return $labelMap[$cat->getId()] ?? $cat->getTitle() ?? '';
            });
        yield BooleanField::new('isPublished', 'Publié');
        yield DateTimeField::new('publishedAt', 'Date de publication')
            ->hideOnIndex();
        yield DateTimeField::new('createdAt', 'Créé le')
            ->hideOnForm();
        yield DateTimeField::new('updatedAt', 'Modifié le')
            ->hideOnForm();
    }
}
