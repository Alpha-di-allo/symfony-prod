<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Order;
use App\Entity\OrderDetails;
use App\Service\MailService;
use App\Repository\OrderRepository;
use App\Repository\PanierRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PanierProduitRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;



class OrderController extends AbstractController
{

    private $order ; 
    private $orderRepository;

    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }


//     #[Route('/order', name: 'app_order')]
// public function newOrder(Request $request, PanierRepository $panierRepository, User $user, EntityManagerInterface $entityManager, PanierProduitRepository $panierProduitRepository): Response
// {
//     // Récupérer l'utilisateur actuel
//     $user = $this->getUser();

//     // Récupérer le panier de l'utilisateur
//     $panier = $panierRepository->findOneBy(['user' => $user]);

//     // Récupérer les produits du panier (PanierProduit)
//     $panierProduits = $panier->getPanierProduits();


//     // Récupérer les produits du panier avec leurs quantités
//     // $panierProduits = $panierProduitRepository->findProductsInPanier($panier);

//     // Générer un numéro de référence automatique pour la commande
//     $length = 8;
//     $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
//     $numRef = '';
//     for ($i = 0; $i < $length; $i++) {
//         $numRef .= $chars[rand(0, strlen($chars) - 1)];
//     }

//     // Calculer le montant total du panier et la TVA
//     $montantTotalPanier = 0;
//     foreach ($panierProduits as $panierProduit) {
//         $prixUnitaire = $panierProduit->getProduct()->getPrix();
//         $quantite = $panierProduit->getQuantity();
//         $montantTotalPanier += $prixUnitaire * $quantite;
//     }
//     $tva = $montantTotalPanier * 0.2; // 20% de TVA

//     // Créer une nouvelle commande
//     $order = new Order();
//     $order->setPanier($panier);
//     $order->setUser($user);
//     $order->setNumRef($numRef);
//     $order->setValidePaiement(false);
//     $order->setOrderStatus("en préparation");
//     $order->setOrderDate(new \DateTimeImmutable());
//     $order->setDateLivraison(new \DateTime());
//     $order->setPrix($montantTotalPanier);

//     // Persister la commande
//     $entityManager->persist($order);

//     // Créer les détails de commande pour chaque produit du panier
//     foreach ($panierProduits as $panierProduit) {
//         $orderDetails = new OrderDetails();
//         $orderDetails->setOrderId($order);
//         $orderDetails->setProduct($panierProduit->getProduct());
//         $orderDetails->setQuantity($panierProduit->getQuantity());
//         $orderDetails->setPrixUnitaire($panierProduit->getProduct()->getPrix());
//         $entityManager->persist($orderDetails);
//     }
    
//         //  vider le panier apres la création de la commande 
//         //   foreach($panierProduits as $panierProduit){
//         //     $entityManager->remove($panierProduit);
//         // }


//     // Flush pour enregistrer les changements dans la base de données
//     $entityManager->flush();

//     // Après la création de la commande, vous pouvez vider le panier si nécessaire
//     // Attention à ne pas supprimer le panierProduits directement ici, car il est déjà utilisé

//     // Récupérer à nouveau le panier après avoir vidé les produits (si nécessaire)
//     $panier = $panierRepository->findOneBy(['user' => $user]);
//     $panierProduits = $panier->getPanierProduits();

//     // Récupérer l'ID de la commande pour l'affichage
//     $orderId = $order->getId();

//     // Rendre la vue avec les détails de la commande et du panier
//     return $this->render('order/index.html.twig', [
//         'order' => $order,
//         'panierProduits' => $panierProduits,
//         'montantTotalPanier' => $montantTotalPanier,
//         'montantTTC' => $tva,
//         'orderId' => $orderId
//     ]);
// }

    
    #[Route('/Facture_mail/{orderId}', name:"app_facture",methods:["POST"])]
    public function envoyerFacture($orderId, MailService $mailService, EntityManagerInterface $entityManager)
    {
       
        // Récupérer la commande à partir de son ID
        $order = $entityManager->getRepository(Order::class)->find($orderId);

        // Récupérer l'utilisateur connecté
        $user = $this->getUser();

        // Envoyer la facture par e-mail en utilisant le service MailService
        $mailService->sendFactureEmail($order, $user);

        // Rediriger l'utilisateur vers une autre page après l'envoi de l'e-mail
        return $this->render('emails/FactureEmails.html.twig',[
             'order'=>$order, 
         ]);
        // return $this->redirectToRoute('app_presentation', ['order' => $order]);
         // Retourner une réponse JSON pour indiquer le succès de l'envoi du mail
        //  return new JsonResponse(['success' => true]);
    }

    #[Route('/my-orders', name: 'app_user_orders')]
    public function userOrders(Request $request): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();

        $currentPage = $request->query->getInt('page', 1); // Numéro de la page actuelle
        $pageSize = 50; // Nombre d'éléments par page
    

        // Récupérer les commandes de l'utilisateur
        $orders = $this->orderRepository->findBy(['user' => $user]);

         // Paginer les résultats manuellement en utilisant array_slice pour obtenir la page actuelle
        $paginatedOrders = array_slice($orders, ($currentPage - 1) * $pageSize, $pageSize);

    // Calculer le nombre total de pages
        $totalPages = ceil(count($orders) / $pageSize);


        // Renvoyer les commandes à un nouveau template Twig
        return $this->render('order/user_orders.html.twig', [
            'orders' => $orders,
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
           
        ]);
    }



#[Route('/order', name: 'app_order')]
public function newOrder(Request $request, PanierRepository $panierRepository, EntityManagerInterface $entityManager, PanierProduitRepository $panierProduitRepository): Response
{
    // Récupérer l'utilisateur actuel
    $user = $this->getUser();

    // Récupérer le panier de l'utilisateur
    $panier = $panierRepository->findOneBy(['user' => $user]);

    if (!$panier) {
        $this->addFlash('error', 'Votre panier est vide.');
        return $this->redirectToRoute('app_panier'); // Remplacez 'panier_route' par la route de votre page panier
    }

    // Récupérer les produits du panier (PanierProduit)
    $panierProduits = $panier->getPanierProduits();

    if (count($panierProduits) === 0) {
        $this->addFlash('error', 'Votre panier est vide.');
        return $this->redirectToRoute('app_panier'); // Remplacez 'panier_route' par la route de votre page panier
    }

    // Calculer le montant total du panier et la TVA
    $montantTotalPanier = 0;
    foreach ($panierProduits as $panierProduit) {
        $prixUnitaire = $panierProduit->getProduct()->getPrix();
        $quantite = $panierProduit->getQuantity();
        $montantTotalPanier += $prixUnitaire * $quantite;
    }

    if ($montantTotalPanier <= 0) {
        $this->addFlash('error', 'Le montant total de la commande doit être supérieur à zéro.');
        return $this->redirectToRoute('app_panier'); // Remplacez 'panier_route' par la route de votre page panier
    }

    $tva = $montantTotalPanier * 0.2; // 20% de TVA

    // Générer un numéro de référence automatique pour la commande
    $length = 8;
    $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $numRef = '';
    for ($i = 0; $i < $length; $i++) {
        $numRef .= $chars[rand(0, strlen($chars) - 1)];
    }

    // Créer une nouvelle commande
    $order = new Order();
    $order->setPanier($panier);
    $order->setUser($user);
    $order->setNumRef($numRef);
    $order->setValidePaiement(false);
    $order->setOrderStatus("en préparation");
    $order->setOrderDate(new \DateTimeImmutable());
    $order->setDateLivraison(new \DateTime());
    $order->setPrix($montantTotalPanier);

    // Persister la commande
    $entityManager->persist($order);

    // Créer les détails de commande pour chaque produit du panier
    foreach ($panierProduits as $panierProduit) {
        $orderDetails = new OrderDetails();
        $orderDetails->setOrderId($order);
        $orderDetails->setProduct($panierProduit->getProduct());
        $orderDetails->setQuantity($panierProduit->getQuantity());
        $orderDetails->setPrixUnitaire($panierProduit->getProduct()->getPrix());
        $entityManager->persist($orderDetails);
    }

    // Flush pour enregistrer les changements dans la base de données
    $entityManager->flush();

    // Rendre la vue avec les détails de la commande et du panier
    return $this->render('order/index.html.twig', [
        'order' => $order,
        'panierProduits' => $panierProduits,
        'montantTotalPanier' => $montantTotalPanier,
        'montantTTC' => $tva,
        'orderId' => $order->getId()
    ]);
}

    
    #[Route('/order/detail/{id}', name: 'app_order_details', methods: ['GET'])]
    public function showOrder(Order $order): Response
    {
        // Vérifier si l'utilisateur connecté est le propriétaire de la commande
        $user = $this->getUser();
        if ($order->getUser() !== $user) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas accéder à cette commande.');
        }

        // Récupérer les détails de la commande
        $orderDetails = $order->getOrderDetails();

        return $this->render('order/show.html.twig', [
            'order' => $order,
            'orderDetails' => $orderDetails,
        ]);
    }






}
