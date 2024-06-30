<?php 

namespace App\Service;

use App\Entity\User;
use App\Repository\MessagesRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Doctrine\ORM\EntityManagerInterface;

class MessageNotif
{
    private $entityManager;
    private $messagesRepository;
    private $tokenStorage; 

    public function __construct(EntityManagerInterface $entityManager,MessagesRepository $messagesRepository,TokenStorageInterface $tokenStorage)
    {
        $this->entityManager = $entityManager;
        $this->messagesRepository = $messagesRepository;
        $this->tokenStorage = $tokenStorage;
    }

    public function messNotif()
    {

        // Récupérer l'utilisateur actuellement authentifié
         $user = $this->tokenStorage->getToken()->getUser();

        // Vérifier si l'utilisateur est bien une instance de User
        if (!$user instanceof User) {
            return 0; // Retourne 0 si l'utilisateur n'est pas connecté
        }

            $message = $this->messagesRepository->findOneBy(['user' =>$user]);

        if (!$message) {
            return 0; // Retourne 0 s'il n'y a pas de panier pour cet utilisateur
        }
              
        // $nombreInBoxMail = count($message->getMessage());

        // return $nombreInPanier;
    }
}
