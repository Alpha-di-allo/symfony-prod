<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use Vich\UploaderBundle\Form\Type\VichImageType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class ProductCrudController extends AbstractCrudController
{

    // public const ACTION_DUPLICATE = "duplicate";
    // public const PRODUCTS_BASE_PATH = 'image_burger/upload/Products';
    // public const PRODUCTS_UPLOAD_DIR = 'public/image_burger/upload/Products';

    public static function getEntityFqcn(): string
    {
        return Product::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('nom'),
            TextEditorField::new('description'),
            MoneyField::new ('prix', 'prix')->setRequired(true)->setCurrency('EUR'),
            IntegerField::new('stock')->setRequired(true),
           
            ImageField::new('image')
            ->setBasePath('images\upload\Products')
            ->setUploadDir('public\images\upload\Products')
            ->setSortable(false),

            
            BooleanField::new('active')->setRequired(true),
            FormField::addPanel('Category'),
           
            AssociationField::new('categorieProduit', 'Catégorie')
            ->setFormType(EntityType::class)
            ->setFormTypeOptions([
                'class' => 'App\Entity\Category',
                'choice_label' => 'nomCategorie', // ou tout autre champ que vous souhaitez afficher
            ]),
            
            //   DateTimeField::new('created_At','date de création')->renderAsChoice(),
            //   DateTimeField::new('update_At','date de modification')->hideOnForm(),            
        ];
    }

    
}
