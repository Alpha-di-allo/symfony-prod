<?php

namespace App\Controller\Api;

use App\Entity\Order;
use App\Entity\OrderDetails;
use App\Repository\OrderRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;



// #[IsGranted('ROLE_USER')]
class OrderApiController extends AbstractController
{
    // #[IsGranted('ROLE_SUPER_ADMIN')]
    #[Route('/api/orders', name: 'api_orders_index', methods: ['GET'])]
    public function apiIndex(OrderRepository $orderRepository): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['message' => 'Utilisateur non connecté'], Response::HTTP_UNAUTHORIZED);
        }

        $roles = $user->getRoles();
        if (!in_array('ROLE_SUPER_ADMIN', $roles)) {
            return $this->json(['message' => 'Accès refusé, rôle manquant'], Response::HTTP_FORBIDDEN);
        }

        // $orders = $orderRepository->findBy(['user' => $user]);
        $orders = $orderRepository->findAll();


        $ordersData = [];

        foreach ($orders as $order) {
            $formattedDate = $order->getDateLivraison() ? $order->getDateLivraison()->format('d/m/Y') : 'Date non disponible';
            $ordersData[] = [
                'id' => $order->getId(),
                'reference' => $order->getNumRef(),
                'user'=>$order->getUser()->getEmail(),
                'status' => $order->getOrderStatus(),
                'date' => $formattedDate,
                'Prix' => $order->getPrix()/100,
            ];
        }

        return $this->json($ordersData);
    }

    #[Route('/api/order', name: 'api_orders_index', methods: ['GET'])]
    public function seeOrder(OrderRepository $orderRepository): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['message' => 'Utilisateur non connecté'], Response::HTTP_UNAUTHORIZED);
        }

        $orders = $orderRepository->findBy(['user' => $user]);
        // $orders = $orderRepository->findAll();


        $ordersData = [];

        foreach ($orders as $order) {
            $formattedDate = $order->getDateLivraison() ? $order->getDateLivraison()->format('d/m/Y') : 'Date non disponible';
            $ordersData[] = [
                'id' => $order->getId(),
                'reference' => $order->getNumRef(),
                'user'=>$order->getUser()->getEmail(),
                'status' => $order->getOrderStatus(),
                'date' => $formattedDate,
                'Prix' => $order->getPrix()/100,
            ];
        }

        return $this->json($ordersData);
    }

   

    #[Route('api/orders/{id}', name: 'api_order_show', methods: ['GET'])]
    public function show($id, OrderRepository $orderRepository, SerializerInterface $serializer,Order $order): JsonResponse
    {
        $order = $orderRepository->find($id);

        if (!$order) {
            return new JsonResponse(['message' => 'Order not found'], Response::HTTP_NOT_FOUND);
        }

        $orderDetails = [];
        foreach ($order->getOrderDetails() as $detail) {
            $product = $detail->getProduct();
            $domainName = $this->getParameter('domain_name');
            $imageUrl = $domainName . 'images/upload/Products/' . $product->getImage();

            $orderDetails[] = [
                'product_name' => $product->getNom(),
                'quantity' => $detail->getQuantity(),
                'unit_price' => $detail->getPrixUnitaire(),
                'image_url' => $imageUrl,
            ];
        }

        return new JsonResponse([
            'orderId' => $order->getId(),
            'user' => $order->getUser()->getEmail(),
            'order_details' => $orderDetails,
        ], Response::HTTP_OK);
    }

}
