<?php 

namespace App\Service;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use App\Repository\PanierRepository;
use Doctrine\ORM\EntityManagerInterface;

class PanierNotif
{
    private $entityManager;
    private $panierRepository;
    private $tokenStorage; 

    public function __construct(EntityManagerInterface $entityManager, PanierRepository $panierRepository,TokenStorageInterface $tokenStorage)
    {
        $this->entityManager = $entityManager;
        $this->panierRepository = $panierRepository;
        $this->tokenStorage = $tokenStorage;
    }

    public function cartNotif()
    {

        // Récupérer l'utilisateur actuellement authentifié
         $user = $this->tokenStorage->getToken()->getUser();

        // Vérifier si l'utilisateur est bien une instance de User
        if (!$user instanceof User) {
            return 0; // Retourne 0 si l'utilisateur n'est pas connecté
        }
        $panier = $this->panierRepository->findOneBy(['user' => $user]);

        if (!$panier) {
            return 0; // Retourne 0 s'il n'y a pas de panier pour cet utilisateur
        }
              
        $nombreInPanier = count($panier->getPanierProduits());

        return $nombreInPanier;
    }
}
