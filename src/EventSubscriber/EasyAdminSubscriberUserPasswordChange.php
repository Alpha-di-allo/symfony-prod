<?php 

namespace App\EventSubscriber;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class EasyAdminSubscriberUserPasswordChange implements EventSubscriberInterface  
{

private $entityManager; 
private $passwordHasher; 

public function __construct(EntityManagerInterface $entityManager,UserPasswordHasherInterface $passwordHasher)
{
   $this->entityManager = $entityManager; 
   $this->passwordHasher = $passwordHasher;  
}

public static function getSubscribedEvents() : array
{
    return [

        BeforeEntityPersistedEvent::class =>['addUser'],    // lorsque l'evenement se produit la méthode addUser est apeller 
        BeforeEntityUpdatedEvent::class =>['updateUser'],   // lorsque l'evenement se produit la méthode updateUser est apeller 
    ];
}

public function UpdateUser(BeforeEntityUpdatedEvent $event):void
{
$entity = $event->getEntityInstance(); // recupere l'instance de l'entité qui est sur le point d'etre mis a jour 

if(!($entity instanceof User)){ // on verifie si l'entité est une instance de la classe user 
    return ;
}
    $this->setPassword($entity); // appelle la methode pour hacher le mot de passe 
}

public function addUser(BeforeEntityPersistedEvent $event):void
{
    $entity =$event->getEntityInstance();
    if(!($entity instanceof User)){
        return ;
    }
    $this->setpassword($entity);
}


public function setPassword(User $entity):void 
{
    $password = $entity->getPassword(); // recupere le mot de passe actuel 
    $entity->setPassword($this->passwordHasher->hashPassword($entity , $password));// utilise le service de hachage  

    $this->entityManager->persist($entity); // on persite 
    $this->entityManager->flush(); // on execute les operations de BDD necessaire pour sauvegarder les changements 
}







}