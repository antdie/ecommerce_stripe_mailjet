<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ProductCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Product::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityPermission('ROLE_SUPER_ADMIN')
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        if (Crud::PAGE_EDIT === $pageName) {
            yield ImageField::new('image')
                ->setLabel('Image*')
                ->setUploadDir('public/uploads/')
                ->setBasePath('uploads/')
                ->setUploadedFileNamePattern('[slug].[timestamp].[extension]')
                ->setFormTypeOption('allow_delete' ,false)
                ->setRequired(false);
        } else {
            yield ImageField::new('image')
                ->setLabel('Image')
                ->setUploadDir('public/uploads/')
                ->setBasePath('uploads/')
                ->setUploadedFileNamePattern('[slug].[timestamp].[extension]')
                ->setSortable(false);
        }
        yield TextField::new('name');
        yield SlugField::new('slug')->setTargetFieldName('name')->hideOnIndex();
        yield AssociationField::new('category')->renderAsNativeWidget();
        yield TextEditorField::new('description');
        yield MoneyField::new('price')->setCurrency('EUR');
        yield BooleanField::new('homepage');
    }
}
