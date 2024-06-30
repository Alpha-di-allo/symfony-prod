<?php

namespace App\Controller;

use Stripe\Charge;
use Stripe\Stripe;
use App\Entity\User;
use App\Entity\Order;
use App\Entity\Panier;


use App\Entity\Product;
use App\Entity\PanierProduit;
use App\Service\stripeService;
use Doctrine\ORM\EntityManager;
use App\Repository\OrderRepository;
use App\Repository\PanierRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PanierProduitRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
// use Symfony\Component\Routing\Generator\UrlGenerator;

class StripeController extends AbstractController

 {
      
    // public function __construct(readonly private string $stripeSk){
    //   Stripe::setApiKey($this->$stripeSk);
    //   Stripe::setApiVersion('2023-10-16');
    // }

    #[Route('/stripe', name: 'app_stripe')]
    public function index(): Response
    {
        return $this->render('stripe/index.html.twig', [
          'stripe_key' => $_ENV["STRIPE_SECRET_KEY_TEST"],
        ]);
    }

    #[Route('stripe/checkout_session/{id}', name :'app_checkout' , methods:['POST','GET'])]
    public function stripeCheckout($id,Request $request, PanierRepository $panierRepository ,EntityManagerInterface $entityManager,Panier $panier,UrlGeneratorInterface $urlGenerator)
    {
     //on recupere l'utilisateur connecter 
        $user=$this->getUser();

 
     
     // Récupérer le Panier de l'utilisateur
       $panier = $panierRepository->findOneBy(['user' => $user]);

      // Assurez-vous que le panier existe
    if (!$panier) {
      throw $this->createNotFoundException('Le panier n\'a pas été trouvé');
  }
     
      // Récupérer les produits du panier de l'utilisateur connecté
        $panierProduits = $panier->getPanierProduits();
        
        
      // Préparer les informations des produits pour la session Stripe
       $lineItems = [];

      // Supposons que $panierProduits est une collection d'objets PanierProduit
      $montantTotalPanier = 0;

          foreach ($panierProduits as $panierProduit) {
              // $prixUnitaire = $panierProduit->getProduct()->getPrix();
              // $quantite = $panierProduit->getQuantity();
              // // Calculer le montant total pour ce produit
              // $montantTotalProduit = $prixUnitaire * $quantite;
              // // Ajouter le montant total de ce produit au montant total du panier
              // $montantTotalPanier += $montantTotalProduit;
              $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => $panierProduit->getProduct()->getPrix() , // Prix en centimes
                    'product_data' => [
                        'name' => $panierProduit->getProduct()->getNom(), // Nom du produit
                    ],
                ],
                'quantity' => $panierProduit->getQuantity(),

            ];
          }

            $tauxTVA = 10; // 10% de TVA
            // Calcul de la TVA
            $tva = ($montantTotalPanier * $tauxTVA)/100;
            // Montant total avec TVA
            $montantTotalAvecTVA = $montantTotalPanier + $tva;

 // Récupérer la clé secrète de Stripe depuis les paramètres de la méthode
 $stripeSk = $this->getParameter('stripe_secret_key');
        
Stripe::setApiKey($stripeSk);


$checkout_session = \Stripe\Checkout\Session::create([
  'payment_method_types' =>['card'],
  'line_items' => $lineItems,
  'mode'=>'payment',
  'success_url' =>$urlGenerator->generate('app_success',[],UrlGeneratorInterface::ABSOLUTE_URL),
  'cancel_url' => $urlGenerator->generate('app_cancel',[],UrlGeneratorInterface::ABSOLUTE_URL) ,
  'automatic_tax' => [
    'enabled' => true,
  ]
]);

        $this->addFlash(
          'success',
          'Payment réussi!'
        );

      return $this->redirect($checkout_session->url, Response::HTTP_SEE_OTHER);
    }


#[Route('/success_url', name: 'app_success')]
public function success(Order $order ,EntityManagerInterface $entityManager): Response
{

// Mettre à jour l'état de paiement de la commande

// $montantTotalPanier = 0;

$order->setValidePaiement(true);
// $order->setPrix($montantTotalPanier);




// Enregistrez les modifications dans la base de données
//  $entityManager->persist($order->setValidePaiement(true));
//  $entityManager->flush();

  return $this->render('stripe/success.html.twig',[]);

}


#[Route('/cancel_url', name: 'app_cancel')]
public function cancel(): Response
 {
   return $this->render('stripe/cancel.html.twig',[]);
 }

}

        
 
