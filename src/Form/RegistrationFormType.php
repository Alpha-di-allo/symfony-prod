<?php

namespace App\Form;

use App\Entity\User;
use App\Service\MatriculeGenerator;
// use Doctrine\DBAL\Types\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class RegistrationFormType extends AbstractType
{
    private $matriculeGenerator;

    public function __construct(MatriculeGenerator $matriculeGenerator)
    {
        $this->matriculeGenerator = $matriculeGenerator;
    }


    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder // instance du constructeur du formulaire 

            ->add('firstname',TextType::class,[
            'attr'=>['class'=>'form-control',
            'placeholder'=>'entrez votre prenom ']
            ])
            ->add('lastname',TextType::class,[
                'attr'=>['class'=>'form-control',
                'placeholder'=>'entrez votre nom']
            ])
            ->add('adresse',TextType::class,[
                'attr'=>['class'=>'form-control',
                'placeholder'=>'entrez votre adresse']
            ])
            ->add('ville',TextType::class,[
                'attr'=>['class'=>'form-control',
                'placeholder'=>'entrez votre ville']
                ])
            ->add('codePostal',IntegerType::class,[
                'attr'=>['class'=>'form-control codeP',
                'placeholder'=>'code postal']
            ])
            ->add('numeroTel',TextType :: class,[
                'attr'=>['class'=>'form-control',
                'placeholder'=>'numero de tel ']
            ])
            ->add('email',TextType ::class,[
                'attr' =>['class'=>'form-control email',
                'placeholder'=>'entrez votre email ']
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'You should agree to our terms.',
                       
                    ]),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password',
                            'class'=>'form-control',
                            'placeholder'=>'entrez votre mot de passe'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un mot de passe ',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('matricule', TextType::class, [
                'label' => 'Matricule',
                'disabled' => true, // Rendre le champ non modifiable
                'required' => false, // Le champ peut être vide
                'attr' => [
                    'class' => 'form-control', // Ajouter des classes CSS au champ si nécessaire
                    'value' => $this->matriculeGenerator->generateMatricule(), // Pré-remplir le champ avec le matricule généré
                ],
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        
        ]);
    }

    
}
