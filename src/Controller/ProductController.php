<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Ingredient;
use App\Form\Product2Type;
use App\Repository\PanierRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/product')]
class ProductController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'app_product_index', methods: ['GET'])]
    public function index(ProductRepository $productRepository): Response
    {
        return $this->render('product/index.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_product_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $product = new Product();
        
        $form = $this->createForm(Product2Type::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
        // Récupération de l'entité category selectionner dans le formulaire 
        $category = $product->getCategorieProduit();
        
          // Vérification si l'entité Category est gérée (persistée)
          if (!$entityManager->contains($category)) {
            // Si elle n'est pas gérée, on la persiste
            $entityManager->persist($category);
        }

            $entityManager->persist($product);
            $entityManager->flush();

            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_product_show', methods: ['GET'])]
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/details/{id}', name: 'app_product_details')]
    public function showDetails($id): Response
    {
        // Récuperer la totalité des ingredients 
        $ingredient = $this->entityManager->getRepository(Ingredient::class)->findAll();
      
         // Récupérer le produit correspondant à l'ID donné
         $product = $this->entityManager->getRepository(Product::class)->find($id);

        // Vérifier si le produit existe
        if (!$product) {
            throw $this->createNotFoundException('Le produit demandé n\'existe pas');
        }

        // Passer les données du produit au template Twig pour affichage
        return $this->render('product_vu_client/product_details/index.html.twig', [
            'product' => $product,
            'ingredient'=>$ingredient
        ]);
    }

    public function addIngredientsToProduct(int $productId, array $ingredientIds): Response
    {
      
         // Récupérer le produit depuis la base de données
         $product = $this->entityManager->getRepository(Product::class)->find($productId);


        if (!$product) {
            throw $this->createNotFoundException('Produit non trouvé pour l\'ID ' . $productId);
        }

        // Récupérer les ingrédients depuis la base de données
        $ingredients = $this->entityManager->getRepository(Ingredient::class)->findBy(['id' => $ingredientIds]);

        // Ajouter chaque ingrédient au produit
        foreach ($ingredients as $ingredient) {
            $product->addIngredient($ingredient);
        }

        // Persister les changements en base de données
        $this->entityManager->flush();

        return new Response('Ingrédients ajoutés avec succès au produit.');
    }


    #[Route('/{id}/edit', name: 'app_product_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(Product2Type::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_product_delete', methods: ['POST'])]
    public function delete(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            $entityManager->remove($product);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
    }


}
