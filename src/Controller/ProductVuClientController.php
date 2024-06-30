<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductVuClientController extends AbstractController
{


    #[Route('/product/vu/client', name: 'app_product_vu_client')]
    public function index(ProductRepository $productRepository,CategoryRepository $categoryRepository): Response

    {

    // $products = $productRepository->findAll();
    // $categories = $categoryRepository->findAll();
    $categories = $categoryRepository->findAllWithProducts(); // Appel d'une méthode custom pour obtenir les catégories avec les produits associés
    

        return $this->render('product_vu_client/index.html.twig', [
            'controller_name' => 'ProductVuClientController',
            // 'products' =>$products, 
            'categories' =>$categories,
        ]);
    }
}
