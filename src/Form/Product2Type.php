<?php

namespace App\Form;

use DateTimeImmutable;
use App\Entity\Product;
use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;


class Product2Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('description')
            ->add('prix')
            ->add('stock')
            ->add('image')
            ->add('created_at', DateType::class, [
                'label' =>'dateCreation',
                'widget' => 'single_text',
                'input' => 'datetime_immutable'
            ])
            ->add('categorieProduit', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'nomCategorie', // Nom de la propriété à afficher dans la liste déroulante
                'placeholder' => 'Choisir une catégorie', // Optionnel : permet d'ajouter un libellé vide
                'required' => true,
                // Autres options de configuration du champ...
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer',
            ]);
         
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
