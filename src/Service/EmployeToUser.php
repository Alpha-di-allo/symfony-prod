<?php 

// src/Service/EmployeToUser.php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class EmployeToUser
{
    private $userRepository;
    private $entityManager;

    public function __construct(UserRepository $userRepository,EntityManagerInterface $entityManager)
    {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
    }

    public function getEmployeUserWithLessUser():?User

    {
        $users = $this->userRepository->findAll(); // on retrouve tous les utilisateur 

        $conseillers = $this->userRepository->findUsersByRoleEmploye(); // on les trie par conseiller 

        if ($conseillers != null) // on verifie que le conseiller existe 
        {
            //selectionner le conseiller avec le moins de client(s)
            
            $result = array();
            foreach ($conseillers as $employe) {
                $contClientEmploye = 0;

                //chercher les client de cet employé
                foreach ($users as $client) {
                    if ($client->getEmployees() != null && ($employe->getId() == $client->getEmployerId())) {
                        $clientConseillerId = $client->getEmployerId();
                        $contClientEmploye += 1; // increment count client de ce employe
                    }
                }

                $employeId = $employe->getId();

                //tableau avec resultat par employé  ex : ( 25 => 2);
                $result = $result + array(
                    $employeId => $contClientEmploye,
                );
            }
        
            $minValue = min($result); // minimale $contClientEmploye (nombre de client) 
            $minKey = array_search($minValue, $result); //$employeId (id d'employe) associé au minimum de nombre de client 
            
            $minKey = intval($minKey);// ici on traduit la valeur de l'id_employe en entier pour pa génerer d'erreur lorque on va vouloir attribuer id_emloyer au nouvelle User dans la base de donnée 

            $conseiller = $this->userRepository->find($minKey);

            return $conseiller;
        }
    }
}


    

    

