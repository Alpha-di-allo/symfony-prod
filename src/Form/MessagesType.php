<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Messages;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class MessagesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        //  $builder

        // Récupérer l'utilisateur connecté
        $user = $options['user'];
        $champActif = $options['champActif'];
        $userClients = $options['userClients'];
        $conseiller = $options['conseiller'];
        
        $builder
            ->add('title',TextType::class,[
                "attr"=>[
                    "class"=>"form-control"  
                ]
            ])
            ->add('message',TextareaType::class,[
                "attr"=>[
                    "class"=>"form-control"
                ]
            ]);

        if($champActif){
            $builder
            ->add('recipient',EntityType::class,[
                "class"=> User::class,
                'choice_label' => 'email',
                'choices' => $userClients, // Liste des clients du conseiller
                'placeholder' => 'Choisir un destinataire',
                'required' => false,
                "attr"=>[
                    "class"=>"form-control",
                ],
            ]);

        }else{
            $builder
            ->add('recipient',EmailType::class,[
                'label' => 'Destinataire',
                'attr' => [
                    'class' => 'form-control',
                    'value' => $conseiller->getEmail(),
                    'readonly' => true,
                ],
                'disabled' => true,
            ]);
        }
         $builder  
            ->add('envoyer',SubmitType::class,[
                "attr"=>[
                    "class"=>"btn btn-primary mt-5"
                ]
                ]);
        
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Messages::class,
            'user'=> null , // Utilisateur connecté null par défaut 
            'clients'=> null,
            'recipient_options' => [], // Ajoutez cette ligne pour définir la valeur par défaut
            'recipient_type' => null, // Ajoutez cette ligne pour définir la valeur par défaut
            'champActif' => false,
            'userClients' => '', 
            'conseiller' => ''
        ]);
    }
}
