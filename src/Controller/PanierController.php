<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Panier;
use App\Entity\Product;
use Doctrine\ORM\Mapping\Id;
use App\Entity\PanierProduit;
use App\Repository\PanierRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PanierProduitRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PanierController extends AbstractController
{
    private $panierRepository;

    public function __construct(PanierRepository $panierRepository) {
        $this->panierRepository = $panierRepository;
    }

    #[Route('/add/{id}',name:'add_panier')]
    public function addPanier( $id, Product $product,PanierRepository $panierRepository,EntityManagerInterface $entityManager,PanierProduitRepository $panierProduitRepository,ProductRepository $productRepository):Response
    {
         // on recupere l'identifiant du produit et on la met dans notre variable product 
         $product = $entityManager->getRepository(Product::class)->findOneBy(['id' => $id]);
       // $product = $productRepository->find($id);

         // on obtient l'utilisateur actuel 
         $user= $this->getUser();
         

         // verifier si l'utilisateur est connnecter 
         if($user){

        // recuperer le panier de l'utilisateur 
        
          $panier = $panierRepository->findOneBy(['user'=>$user]);
            

          // recuperer le panierProduct c'est a dire la liste des produits dans le panier et leur quantite    
          $panierProduit= $panierProduitRepository->showPanierProduit($panier,$product);
     

          //si panierProduit existe et qu'on veut ajouter un produit on incremente
      if($panierProduit){
          $panierProduit->setQuantity($panierProduit->getquantity()+1);//tu m'ajoute le produit de 1 de sa quantite initiale 
           $entityManager->persist($panierProduit);
           $entityManager->flush();
      }else{
          //sinon tu me crée une instance de panierProduit et tu me donne le produit dans panierProduit 
          $panierProduit = new PanierProduit(); // on cree l'objet
          
          $panierProduit->setPanier($panier); // il l'existe pas donc on le set pour le recuperer 
          $panierProduit->setProduct($product); // idem pour le produit 
          $panierProduit->setQuantity(1) ;// on cree une quantité minimale a l'objet une fois ajouter
          
          $panier->addPanierProduit($panierProduit);//on met le contenu de panierProduit dans panier  
          $entityManager->persist($panierProduit); // on persiste les données 
          $entityManager->flush(); // on push en bdd
      }

      $entityManager->persist($panierProduit); // on persiste les données 
      $entityManager->flush(); // on push en bdd

          //on redirige vers la page du panier 
          return $this->redirectToRoute('app_panier');
        }else{
            // return$this->redirectToRoute('app_login');
            return new RedirectResponse($this->generateUrl('app_login'));
        }
    }


    #[Route('/panier', name: 'app_panier')]
    public function index(PanierRepository $panierRepository,PanierProduitRepository $panierProduitRepository, PanierProduit $panierProduit,Product $product,User $user): Response
    {
         //on recupere l'utilisateur connecter 
         $user=$this->getUser();

         // on verifie si l'utilisateur est connecter 
        if($user){

            //on recupere le panier de l'utilisateur 
             $panier= $panierRepository->findOneBy(['user'=>$user]);

            // verifier si les produit du panier existe 
            if($panier){
                $panierProduit = $panier->getPanierProduits();
            }  
            
            // Récupérer les produits du panier de l'utilisateur connecté
            $panierProduits = $panier->getPanierProduits();

            

            // Supposons que $panierProduits est une collection d'objets PanierProduit
            $montantTotalPanier = 0;
            $tva = 0; // Initialisation de la variable $tva à 0
          

            foreach ($panierProduits as $panierProduit) {
                $prixUnitaire = $panierProduit->getProduct()->getPrix();
                $quantite = $panierProduit->getQuantity();
                
                // Calculer le montant total pour ce produit
                $montantTotalProduit = $prixUnitaire * $quantite;

                // Ajouter le montant total de ce produit au montant total du panier
                $montantTotalPanier += $montantTotalProduit;
                $tauxTVA = 20; // 20% de TVA
                $tva = ($montantTotalPanier * $tauxTVA)/100;
                $montantTotalAvecTVA = $montantTotalPanier + $tva;
            }
            
            
         


            return $this->render('panier/index.html.twig', [
            'controller_name' => 'PanierController',
            'panierProduit'=>$panierProduit,
            'panierProduits'=>$panierProduits,
            // 'total'=>$montantTotalProduit,
            'montantTotalPanier'=>$montantTotalPanier,
            'product'=>$product,
            'panier' =>$panier,
            // 'montantTTC'=>$montantTotalAvecTVA,
            'tva'=>$tva
        ]);
        }
       
        return $this->redirectToRoute('app_login');

    }

    #[Route('/panier/{id}', name:'delete_panier')]
    public function supprimerProduit($id ,PanierProduitRepository $panierProduitRepository,PanierRepository $panierRepository,EntityManagerInterface $entityManager): Response
    {
         //on recupere l'utilisateur connecter 
         $user=$this->getUser();

         // on verifie si l'utilisateur est connecter 
        if($user){
            //on recupere le panier de l'utilisateur 
            
            $panierRepository->findOneBy(['user'=>$user]);
            $panierProduit= $panierProduitRepository->find($id);

       
       
            //  si le produit du panier n'existe pas on cree une exeption 
            if (!$panierProduit) {
                throw $this->createNotFoundException('Produit du panier non trouvé.');
            }

            // pour supprimer le produit du panier
            $entityManager->remove($panierProduit);
            $entityManager->flush();

            // Redirigez ensuite vers la page du panier
        return $this->redirectToRoute('app_panier');
        }
    }

    #[Route('/less/{id}', name:'lessOne_app')]
    public function lessOne($id,Product $product , EntityManagerInterface $entityManager,PanierRepository $panierRepository,PanierProduitRepository $panierProduitRepository)
    {
    
       // on recupere l'identifiant du produit et on la met dans notre variable product 
        $entityManager->getRepository(Product::class)->findOneBy(['id' => $id]);

      // on recupere l'utilisateur 
      $user= $this->getUser();
         

      // verifier si l'utilisateur est connnecter 
     if($user){

                // recuperer le panier de l'utilisateur 
                $panier = $panierRepository->findOneBy(['user'=>$user]);

                // on recupere le produitPanier en fonction de l'id du produit  et du panier de l'utilisateur 
                $panierProduit= $panierProduitRepository->findOneBy(['produit'=>$id,'panier'=>$panier]);

                
                

                if($panierProduit)
                {// si le produit du panier est deja existant 
                $panierProduit->setQuantity($panierProduit->getquantity()-1);//tu me réduit  le produit de 1 de sa quantite de depart  
                $entityManager->persist($panierProduit);// tu persiste les données 
                $entityManager->flush();// tu le push en base de données 
                }
                else{
                throw $this->createNotFoundException('Ce Produit ne se trouve pas dans votre panier.');
                }

                return $this->redirectToRoute('app_panier');
            }
     }


    
}

