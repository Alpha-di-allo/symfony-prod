<?php 
// src/EventListener/UserMatriculeListener.php

namespace App\EventListener;

use App\Entity\User;
use App\Service\MatriculeGenerator;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class UserMatriculeListener
{
    private $matriculeGenerator;

    public function __construct(MatriculeGenerator $matriculeGenerator)
    {
        $this->matriculeGenerator = $matriculeGenerator;
    }

    public function prePersist(User $user, LifecycleEventArgs $args)
    {
        // Générer un matricule en utilisant le service
        $matricule = $this->matriculeGenerator->generateMatricule();

        // Assigner le matricule à l'utilisateur
        $user->setMatricule($matricule);
    }
}
