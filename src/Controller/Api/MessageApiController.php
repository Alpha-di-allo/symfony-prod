<?php

namespace App\Controller\Api;

use App\Entity\Messages;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\MessagesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

// #[IsGranted('ROLE_USER')]
class MessageApiController extends AbstractController
{
    private $messageRepository; 
    private $userRepository;
    private $security;
    private $entityManager;


    public function __construct(MessagesRepository $messageRepository,EntityManagerInterface $entityManager, UserRepository $userRepository, Security $security)
    {
        $this->messageRepository = $messageRepository; 
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->security = $security;
    }

    // #[IsGranted('ROLE_SUPER_ADMIN')]
    #[Route('/api/messages', name: 'api_messages_index', methods: ['GET'])]   // Super Admin seulement qui voit tous les message de tous le monde 
    public function apiIndex(SerializerInterface $serializer): JsonResponse
    {
        // Vérification de l'utilisateur connecté
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['message' => 'Utilisateur non connecté'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        // Récupération des messages
        $allMessages = $this->messageRepository->findAll($user);

        if (!$allMessages) {
            return $this->json(['message' => 'Aucun message trouvé'], JsonResponse::HTTP_NOT_FOUND);
        }

        // Préparation des données de réponse
        $allMessageData = [];
        foreach ($allMessages as $message) {
            $formattedDate = $message->getCreatedAt() ? $message->getCreatedAt()->format('d/m/Y') : 'Date non disponible';
            $allMessageData[] = [
                'id' => $message->getId(),
                'envoyeur' => $message->getSender()->getEmail(),
                'destinataire'=>$message->getRecipient()->getEmail(),
                'content' => $message->getMessage(),
                'date' => $formattedDate,
            ];
        }

        // Sérialisation et réponse JSON
        $allMessageJson = $serializer->serialize($allMessageData, 'json');

        return new JsonResponse($allMessageJson, JsonResponse::HTTP_OK, [], true);
    }


    #[Route('/api/messages/sent', name: 'api_show_messages_sent', methods: ['GET'])]   // Voir les Message Envoyer de l'utilsateur 
    public function getSentMessages(SerializerInterface $serializer): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['message' => 'Utilisateur non connecté'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $sentMessages = $this->messageRepository->findSentMessagesByUser($user);

        if (!$sentMessages) {
            return $this->json(['message' => 'Aucun message envoyé trouvé'], JsonResponse::HTTP_NOT_FOUND);
        }

        $sentMessagesData = [];
        foreach ($sentMessages as $message) {
            $formattedDate = $message->getCreatedAt() ? $message->getCreatedAt()->format('d/m/Y') : 'Date non disponible';
            $sentMessagesData[] = [
                'id' => $message->getId(),
                'destinataire' => $message->getRecipient()->getEmail(),
                'content' => $message->getMessageContent(),
                'date' => $formattedDate,
            ];
        }

        $sentMessagesJson = $serializer->serialize($sentMessagesData, 'json');

        return new JsonResponse($sentMessagesJson, JsonResponse::HTTP_OK, [], true);
    }

    #[Route('/api/messages/received', name: 'api_show_messages_received', methods: ['GET'])]  // Voir les  Message Recu de l'utilsateur 
    public function getReceivedMessages(SerializerInterface $serializer): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['message' => 'Utilisateur non connecté'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $receivedMessages = $this->messageRepository->findReceivedMessagesByUser($user);

        if (!$receivedMessages) {
            return $this->json(['message' => 'Aucun message reçu trouvé'], JsonResponse::HTTP_NOT_FOUND);
        }

        $receivedMessagesData = [];
        foreach ($receivedMessages as $message) {
            $formattedDate = $message->getCreatedAt() ? $message->getCreatedAt()->format('d/m/Y') : 'Date non disponible';
            $receivedMessagesData[] = [
                'id' => $message->getId(),
                'sender' => $message->getSender()->getEmail(),
                'content' => $message->getMessageContent(),
                'date' => $formattedDate,
            ];
        }

        $receivedMessagesJson = $serializer->serialize($receivedMessagesData, 'json');

        return new JsonResponse($receivedMessagesJson, JsonResponse::HTTP_OK, [], true);
    }


    
    
    #[Route('/api/message/{id}', name: 'api_message_show_this', methods: ['GET'])]  // Voir un Message en particulier 
    public function show(Messages $message): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['message' => 'Utilisateur non connecté'], Response::HTTP_UNAUTHORIZED);
        }

        if ($message->getSender() !== $user) {
            return $this->json(['message' => 'Accès non autorisé'], Response::HTTP_FORBIDDEN);
        }

        if (!$message) {
            return $this->json(['message' => 'Message non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $formattedDate = $message->getCreatedAt() ? $message->getCreatedAt()->format('d/m/Y') : 'Date non disponible';
        
        $messageDetails = [
            'envoyeur' => $message->getSender()->getEmail(),
            'destinataire'=>$message->getRecipient()->getEmail(),
            'content' => $message->getMessage(),
            'date' => $formattedDate,
        ];

        return $this->json($messageDetails);
    }


    //_____________________________________METHODE POST__________________________________________________________________________________

    // #[IsGranted('ROLE_USER')]
    // #[Route('api/messages/go-message', name: 'send_message', methods: ['POST'])] // Envoyer un Message a son Conseiller attribuer 
    // public function sendMessage(Request $request): Response
    // {
    //     $user = $this->getUser();

    //     if (!$user) {
    //         return new JsonResponse(['message' => 'Utilisateur non connecté'], Response::HTTP_UNAUTHORIZED);
    //     }

    //     $conseiller = $user->getEmployer();

    //     if (!$conseiller) {
    //         return new JsonResponse(['message' => 'Aucun conseiller trouvé pour cet utilisateur'], Response::HTTP_BAD_REQUEST);
    //     }
    //     $data = json_decode($request->getContent(), true);
    //     if (!isset($data['content'])) {
    //         return new JsonResponse(['message' => 'Le contenu du message est requis'], Response::HTTP_BAD_REQUEST);
    //     }

    //     $content = $data['content'];                                                       
    //     $message = new Messages();

    //     $message->setSender($user);
    //     $message->setRecipient($conseiller);
    //     $message->setTitle($content);
    //     $message->setMessageContent($content);
    //     $message->setCreatedAt(new \DateTimeImmutable());

    //     $this->entityManager->persist($message);
    //     $this->entityManager->flush();

    //     return new Response('Message Envoyé avec Success', Response::HTTP_CREATED);
    // }

    // #[Route('api/messages/go-message', name: 'send_message', methods: ['POST'])]
    // public function sendMessage(Request $request): Response
    // {
    //     $user = $this->getUser();
    
    //     if (!$user) {
    //         return new JsonResponse(['message' => 'Utilisateur non connecté'], Response::HTTP_UNAUTHORIZED);
    //     }
    
    //     $data = json_decode($request->getContent(), true);
    //     if (!isset($data['content']) || !isset($data['recipientEmail'])) {
    //         return new JsonResponse(['message' => 'Le contenu du message et l\'email du destinataire sont requis'], Response::HTTP_BAD_REQUEST);
    //     }
    
    //     $content = $data['content'];
    //     $recipientEmail = $data['recipientEmail'];
    
    //     // Vérifiez si l'utilisateur est un administrateur
    //     if (in_array('ROLE_ADMIN', $user->getRoles())) {
    //         // Récupérez le client par son email
    //         $client = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $recipientEmail]);
    
    //         if (!$client) {
    //             return new JsonResponse(['message' => 'Client non trouvé'], Response::HTTP_BAD_REQUEST);
    //         }
    
    //         // Vérifiez que le client est attribué à cet administrateur
    //         if ($client->getEmployer() !== $user) {
    //             return new JsonResponse(['message' => 'Ce client n\'est pas attribué à ce conseiller'], Response::HTTP_FORBIDDEN);
    //         }
    //     } else {
    //         // Si l'utilisateur n'est pas administrateur, envoyez le message à un de ses clients attribués
    //         $clients = $user->getClients(); // Suppose que vous avez une relation définissant les clients attribués à l'utilisateur
    //         $client = null;
    
    //         foreach ($clients as $assignedClient) {
    //             if ($assignedClient->getEmail() === $recipientEmail) {
    //                 $client = $assignedClient;
    //                 break;
    //             }
    //         }
    
    //         if (!$client) {
    //             return new JsonResponse(['message' => 'Ce client n\'est pas attribué à cet utilisateur'], Response::HTTP_FORBIDDEN);
    //         }
    //     }
    
    //     $message = new Messages();
    //     $message->setSender($user);
    //     $message->setRecipient($client);
    //     $message->setTitle($content);
    //     $message->setMessageContent($content);
    //     $message->setCreatedAt(new \DateTimeImmutable());
    
    //     $this->entityManager->persist($message);
    //     $this->entityManager->flush();
    
    //     return new Response('Message Envoyé avec Success', Response::HTTP_CREATED);
    // }
    
    #[Route('api/messages/go-message', name: 'send_message', methods: ['POST'])]
public function sendMessage(Request $request): Response
{
    $user = $this->getUser();
    
    if (!$user) {
        return new JsonResponse(['message' => 'Utilisateur non connecté'], Response::HTTP_UNAUTHORIZED);
    }
    
    $data = json_decode($request->getContent(), true);
    if (!isset($data['content']) || !isset($data['recipientEmail'])) {
        return new JsonResponse(['message' => 'Le contenu du message et l\'email du destinataire sont requis'], Response::HTTP_BAD_REQUEST);
    }
    
    $content = $data['content'];
    $recipientEmail = $data['recipientEmail'];
    
    // Vérifiez si l'utilisateur est un administrateur
    if (in_array('ROLE_ADMIN', $user->getRoles())) {
        // Récupérez le client par son email
        $client = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $recipientEmail]);
        
        if (!$client) {
            return new JsonResponse(['message' => 'Client non trouvé'], Response::HTTP_BAD_REQUEST);
        }
        
        // Vérifiez que le client est attribué à cet administrateur
        if ($client->getEmployer() !== $user) {
            return new JsonResponse(['message' => 'Ce client n\'est pas attribué à ce conseiller'], Response::HTTP_FORBIDDEN);
        }
    } else {
        // Si l'utilisateur est un client, envoyez le message à son conseiller
        $advisor = $user->getEmployer(); // Suppose que l'utilisateur (client) a une relation définissant son conseiller
        
        if (!$advisor) {
            return new JsonResponse(['message' => 'Aucun conseiller attribué à cet utilisateur'], Response::HTTP_FORBIDDEN);
        }
        
        if ($advisor->getEmail() !== $recipientEmail) {
            return new JsonResponse(['message' => 'Vous ne pouvez envoyer des messages qu\'à votre conseiller attribué'], Response::HTTP_FORBIDDEN);
        }
        
        $client = $advisor;
    }
    
    $message = new Messages();
    $message->setSender($user);
    $message->setRecipient($client);
    $message->setTitle($content); // Vous pouvez ajuster ce champ selon vos besoins
    $message->setMessageContent($content);
    $message->setCreatedAt(new \DateTimeImmutable());
    
    $this->entityManager->persist($message);
    $this->entityManager->flush();
    
    return new Response('Message Envoyé avec Success', Response::HTTP_CREATED);
}





}
