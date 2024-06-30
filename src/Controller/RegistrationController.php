<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Panier;
use App\Service\SendMailService;
use Symfony\Component\Mime\Email;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Service\MatriculeGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Loader\Configurator\mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;


class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager,MatriculeGenerator $matriculeGenerator,AuthorizationCheckerInterface $authorizationChecker,UserRepository $userRepository,MailerInterface $mailer): Response
    {
       


        // retrouver l'ensemble des utilisateurs 
        $users = $userRepository->findAll();
        
        // on fait appelle a notre methode qui trie les user selon leur role 
        $conseillers = $userRepository->findUsersByRoleEmploye();

        // on verifie que le conseiller existe 
        if ($conseillers != null) 
        {
            //selectionner le conseiller avec le moins de client(s)
            // pour sa on va avoir besoin de comparer les admins entre eux et leur nombre de client qu'ils onts       


            $result = array(); // j'établi un tableau associatif utilisé pour stocker le nombre de client pour chaque conseiller 
            foreach ($conseillers as $employe) {

                //compteur du nombre de client de chaque conseiller 
                $contClientEmploye = 0;

                //chercher le client de cet employé
                foreach ($users as $client) {
                // on verifie si le client a des client associé et surtout on regarde si l'id du conseiller actuel est egal a lid du conseiller attribuer au client 
                    if ($client->getClients() != null && ($employe->getId() == $client->getEmployerId())) {
                        $clientConseillerId = $client->getEmployerId();
                        $contClientEmploye += 1; // incremente le compteur du client pour le conseiller actuel si les conditions sont remplies 
                    }
                }
                
                // obtenir l'id de l'employe 
                $employeId = $employe->getId();

                //tableau avec resultat par employé
                $result = $result + array(   //  L'opérateur + est utilisé pour ajouter une nouvelle clé-valeur au tableau associatif $result 
                    $employeId => $contClientEmploye,
                );
            }
        
            $minValue = min($result); // on veut trouver la valeur minimal dans le tableau result . fonction min renvoi la plus petite valeur dans celle du tableau 
            $minKey = array_search($minValue, $result); // On cherche la clef associé a la valeur minimale . $employeId (id d'employe) associé au minimum de nombre de client 
            
            $minKey = intval($minKey);// On s'assure que la clé est un entier . intval() convertit une valeur en entier ici on traduit la valeur de l'id_employe en entier pour pas génerer d'erreur lorque on va vouloir attribuer id_emloyer au nouvelle User dans la base de donnée 

            $conseiller = $userRepository->find($minKey); // Trouver le conseiller avec la valeur minimal du nombre de client 
        }
        
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($this->getUser()) {
            $this->addFlash('info', 'Vous êtes déjà inscrit et connecté.');
            return $this->render('registration/alreadyRegister.html.twig',[
                'registrationForm' => $form->createView(),
                ]);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            // on genere le matricule via le service creer et sa fonction  
            $matricule = $matriculeGenerator->generateMatricule();
            $user->setMatricule($matricule);

            // ajout du ROLE_USER par defaut pour chaque nouvelle utilisateur si il n'a pas le ROLE_ADMIN 
            if (!$authorizationChecker->isGranted('ROLE_ADMIN')||!$authorizationChecker->isGranted('ROLE_SUPER_ADMIN')) {
                $user->setRoles(['ROLE_USER']);
            }

            $conseiller->addClient($user);

             $entityManager->persist($user);
             $entityManager->flush();        

             $panier = new Panier();
             $panier->setUser($user);
             $entityManager->persist($panier);
             $entityManager->flush();

            

            // do anything else you need here, like send an email

            // $mail->send(
            //     'ventalisburger@gmail.com',
            //     $user->getEmail(),
            //     'Activation de votre compte sur notre site Ventalis Burger',
            //     'register',
            //     compact('user')
            // );


            $email = (new Email())
            ->from('ventalisburger@gmail.com')
            ->to($user->getEmail())
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject('Activation de votre compte sur notre site 
            !')
            ->text('Faite le plein de burger et profitez d\un gout unique !')
            ->html('<p>See Twig integration for better HTML integration!</p>');

        $mailer->send($email);



            $this->addFlash('success', 'Inscription réussie ! Vous êtes maintenant connecté.');

            return $this->redirectToRoute('app_accueil');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}


