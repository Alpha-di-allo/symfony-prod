<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Messages;
use App\Form\MessagesType;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MessagesController extends AbstractController

{

    #[Route('/messages', name: 'app_messages')]
    public function index(): Response
    {
        if (!$this->getUser()) {
            $this->addFlash('notice', 'Connectez-vous pour voir vos messages');
        }

        return $this->render('messages/index.html.twig', [
            'controller_name' => 'MessagesController',
        ]);
    }

    #[Route('/messages/send' , name: 'app_send')]
    public function send(Request $request,EntityManagerInterface $entityManager): Response
    {   
        //Recupérer l 'utilisateur connecter 
        $user = $this->getUser();

        // Vérifier si l'utilisateur est connecté
        if (!$user)
        {
            $this->addFlash('notice', 'Connectez-vous pour envoyer des messages');
            return $this->redirectToRoute('app_login'); // Rediriger vers la page de connexion
        }

        
        //Initialiser les variables pour le conseiller et le client 
        $userClients = $conseiller = '';
        
        //Determiné le role de l'utilisateur  
        $roles = $user->getRoles();

        // Creer un nouveau message 
        $message = new Messages;
                
        if(in_array('ROLE_EMPLOYE',$roles) || in_array('ROLE_ADMIN',$roles)){
            $champActif = true;
            // Récupérer les users du conseiller 
            $userClients = $user->getClients();
        
             // Si le conseiller  n'a pas de client , afficher un message d'erreur
            if ($user->getClients()->isEmpty())
            {
                $this->addFlash('error', 'Vous n\'avez pas d\'utilisateur assigné.');
                return $this->redirectToRoute('app_messages');
            }  

        }else{

            $champActif = false;
            // Récupérer le conseiller  de l'utilisateur connecté 
            $conseiller = $user->getEmployer();

            // Si l'utilisateur n'a pas de conseiller, afficher un message d'erreur
            if (!$conseiller)
            {
                $this->addFlash('error', 'Vous n\'avez pas de conseiller assigné.');
                return $this->redirectToRoute('app_messages');
            }  
        }

        // Déterminer le destinataire en fonction du rôle de l'utilisateur
        if ($conseiller)
        {
        $message->setRecipient($conseiller);
        }

        $form = $this->createForm(MessagesType::class, $message, ['champActif' => $champActif, 'userClients' => $userClients, 'conseiller' => $conseiller ]);
        $form->handleRequest($request);

        // si le formulaire est soumis et valide 
        if ($form->isSubmitted() && $form->isValid())
        { 
           
            // définir l'utilisateur connecté comme l'expéditeur du message 
            $message->setSender($user);


            // persister le message en base de donnée 
            $entityManager->persist($message);
            $entityManager->flush();

            // Ajouter un message flash de succes
            $this->addFlash("message","Message envoyé avec success");

            //Rediriger vers la page de message 
             return $this->redirectToRoute('app_messages');
        }       

            //si le formulaire n'est pas soumis ou invalide , afficher le formulaire 
        return $this->render("messages/send.html.twig",[
            "form"=> $form->createView()
        ]);  
    }  
    


    #[Route('/messages/received', name:"app_received")]
     public function received():Response 
     {
         if (!$this->getUser()) {
             $this->addFlash('notice', 'Connectez-vous pour voir vos messages');
         }

         return $this->render('messages/received.html.twig');
     }
    
     #[Route('/messages/read/{id}', name:'app_read')]
     public function read(Messages $message,EntityManagerInterface $entityManager):Response
     {
         // on modifie le statut du message a lu 
         $message->setIsRead(true);
         
       // on le persite en BDD
         $entityManager->persist($message);
         $entityManager->flush();


         return $this->render('messages/read.html.twig',compact("message")); // compact permet de créer un tableau a partir des variable et de leur valeurs 
     }

    
     #[Route('/message/delete/{id}',name:'app_deleteMessage')]
     public function delete(Messages $message,EntityManagerInterface $entityManager):Response
     {
          $entityManager->remove($message);
          $entityManager->flush();
         return $this->redirectToRoute('app_received');
      }

      #[Route('/messages/sent', name:"app_sent")]
      public function sent():Response 
      {
         if (!$this->getUser()) {
             $this->addFlash('notice1', 'Connectez-vous pour envoyer des messages');
         }
          return $this->render('messages/sent.html.twig');
      }


}
