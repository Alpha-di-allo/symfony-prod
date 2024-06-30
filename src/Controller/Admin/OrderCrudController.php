<?php
// src/Controller/Admin/OrderCrudController.php

namespace App\Controller\Admin;

use App\Entity\Order;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use Symfony\Component\Validator\Constraints\NotBlank;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class OrderCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string

    {
        return Order::class;
    }

    public function configureFields(string $pageName): iterable

    {
        $fields = 
            [
                IdField::new('id')->hideOnForm(),
                AssociationField::new('user'),
                TextField::new('numRef'),
                // TextField::new('orderStatus'),
                MoneyField::new ('prix', 'prix')->setRequired(true)->setCurrency('EUR'),
                
                BooleanField::new('validePaiement'),
                // Ajoutez d'autres champs que vous souhaitez afficher ou modifier
                
                ChoiceField::new('orderStatus')
                ->setChoices([
                    'en Attente ' => 'en Attente ',
                    'en Traitement' => 'en Traitement',
                    'completée' => 'completée',
                    'Annuler' => 'Annuler',
                ]),
            TextareaField::new('adminComment')
                ->setLabel('Commentaire Administrateur')
                ->setFormType(TextareaType::class)
                ->setFormTypeOption('constraints', [
                    new NotBlank([
                        'message' => 'Vous devez laisser un Commentaire .',
                    ]),
                ])
                ->setHelp('Ajouter des information ou un commentaire sur la commande ici.'),

                // ->onlyWhenUpdating(),
            ];
        
        return $fields;
    }

    // public function updateEntity($entityInstance): void
    // {
    //     if ($entityInstance instanceof Order) {
    //         if ($entityInstance->getAdminComment() === null) {
    //             throw new \Exception('Un commentaire est obligatoire lors de la modification du statut de la commande.');
    //         }
    //     }

    //     parent::updateEntity($entityInstance);
    // }
}   
