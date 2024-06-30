<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Service\EmployeToUser;
use App\Service\MatriculeGenerator;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserCrudController extends AbstractCrudController
{
    private $passwordHasher;
    private $matriculeGenerator; 
    private $employeToUser;
   
    
    public function __construct(UserPasswordHasherInterface $passwordHasher,MatriculeGenerator $matriculeGenerator,EmployeToUser $employeToUser)
    {
       $this->passwordHasher = $passwordHasher;
       $this->matriculeGenerator = $matriculeGenerator;
       $this->employeToUser = $employeToUser;

      
       
    
    }

    public function createEntity(string $entityFqcn)
    {
        $user = new User();

        // générer un numéro de matricule 
        $matricule = $this->matriculeGenerator->generateMatricule();
        $user->setMatricule($matricule);

        // Définissez un mot de passe par défaut
        $plainPassword = '';


        // Hasher le mot de passe
        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        

        // Définissez le mot de passe haché dans l'entité User
        $user->setPassword($hashedPassword);

        
        return $user;
    
    }

    public static function getEntityFqcn(): string
    {
       
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('firstname'),
            TextField::new('lastname'),
            TextField::new('numero_tel'),
            TextField::new('email'),
            Field::new('password')->setFormType(PasswordType::class)->onlyOnForms(),
            ChoiceField::new('roles')->renderAsBadges()->allowMultipleChoices()->setChoices([
                'Client'=>'ROLE USER',
                'Employé '=>'ROLE_ADMIN',
                'Admin'=>'ROLE_SUPER_ADMIN']),
            TextField::new('adresse'),
            TextField::new('ville'),
            IntegerField::new('code_postal'),
            TextEditorField::new('matricule')->setFormTypeOptions(['disabled'=>true]),
            // BooleanField::new('Utilisateur Active'),
            // TextField::new('employerId.email')->hideOnForm(),
                             
        ];

    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if(!$entityInstance instanceof User){
            return; 
        }

        // $entityInstance->setRegistrationDate(new \DateTimeImmutable);

        $conseiller = $this->employeToUser->getEmployeUserWithLessUser();
    
    }
}
