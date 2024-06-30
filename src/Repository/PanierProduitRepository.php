<?php

namespace App\Repository;

use App\Entity\PanierProduit;
use App\Entity\Panier;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PanierProduit>
 * @ORM\Repository
 * @method PanierProduit|null find($id, $lockMode = null, $lockVersion = null)
 * @method PanierProduit|null findOneBy(array $criteria, array $orderBy = null)
 * @method PanierProduit[]    findAll()
 * @method PanierProduit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */


class PanierProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PanierProduit::class);
    }

    public function showPanierProduit($panier, $produit ) // montre moi les porduits dans le panier 
    {
        return $this->findOneBy([
            'panier' => $panier,
            'produit' => $produit,
        
        ]);
    }

    public function orderShowPanierProduits($panier)
    {
        return $this->findBy([
        'panier' => $panier,
        ]);
    }

     /**
     * Retourne les produits associés à un panier avec leurs quantités.
     *
     * @param Panier $panier Le panier pour lequel on veut récupérer les produits.
     * @return array Tableau des objets PanierProduit associés au panier.
     */
    public function findProductsInPanier(Panier $panier): array
    {
        return $this->createQueryBuilder('pp')
            ->where('pp.panier = :panier')
            ->setParameter('panier', $panier)
            ->getQuery()
            ->getResult();
    }

   
}
