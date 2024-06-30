<?php

namespace App\Controller;

use App\Entity\OrderDetails;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OrderDetailsController extends AbstractController
{
    // #[Route('/order/details', name: 'app_order_details')]
    // public function index(): Response
    // {
    //     return $this->render('order_details/index.html.twig', [
    //         'controller_name' => 'OrderDetailsController',
    //     ]);
    // }


    // #[Route('/order/detail/{id}', name: 'app_order_details', methods: ['GET'])]
    // public function showOrder(OrderDetails $orderDetails): Response
    // {
    //     // Récupérer la commande associée à OrderDetails
    //     $order = $orderDetails->getOrderId();

    //     // Vérifier si l'utilisateur connecté est le propriétaire de la commande
    //     $user = $this->getUser();
    //     if ($order->getUser() !== $user) {
    //         throw $this->createAccessDeniedException('Vous ne pouvez pas accéder à cette commande.');
    //     }

    //     return $this->render('order/show.html.twig', [
    //         'order' => $order,
    //         'orderDetails' => $orderDetails,
    //     ]);
    // }


}
