<?php
// src/Controller/TermsController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ThermsController extends AbstractController
{
    #[Route('/terms', name: 'app_terms')]
    public function index(): Response
    {
        return $this->render('therms/index.html.twig', [
            'controller_name' => 'TermsController',
        ]);
    }
}
