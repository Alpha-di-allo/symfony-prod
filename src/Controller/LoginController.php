<?php

namespace App\Controller;

use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LoginController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function index(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError(); // recuperation de la derniere erreur d'authentification lors de la tentative de connexion 
        $lastUsername = $authenticationUtils->getLastUsername(); // récupération du dernier nom d'utilisateur saisi lors de la tentative de connexion . Sa prérempli le champs 

        return $this->render('login/index.html.twig', [
            'last_username' => $lastUsername,
            'error'         => $error,  // utilisé pour afficher le message d"erreur a l'utiisateur si la tentative de connexion a echoué 
        ]);
    }
}
