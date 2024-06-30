<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Service\EmployeToUser;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class StaffToUser implements EventSubscriberInterface
{
private $employeToUser;  


public function __construct(EmployeToUser $employeToUser)
{
    $this->employeToUser = $employeToUser; 

}

public static function getSubscribedEvents(): array
    {
        return [
            BeforeEntityPersistedEvent::class => ['addEmployeToUser'],
        ];
    }

public function addEmployeToUser(BeforeEntityPersistedEvent $event):void
    {
        $entity = $event->getEntityInstance();

        if( $entity instanceof User && in_array('ROLE_USER',$entity->getRoles(),true))
        {
            // recuperer via mon service un utilisateur employer avec le moins d' USER 
            $employer = $this->employeToUser->getEmployeUserWithLessUser();
            
            // Associer l'utilisateur avec le rôle USER à l'utilisateur EMPLOYE qui a le moins d'utilisateurs associés
            if ($employer instanceof User) {
                //on donne au USER son conseiller 
                    $entity->setEmployer($employer);

             // Définir la valeur de employer_id (le conseiler)
             $entity->setEmployerId($employer->getId());
             
             //on donne a l'ADMIN (le conseiller ) le nouveau USER
             $employer->addClient($entity);
            }

        }
    }

}

