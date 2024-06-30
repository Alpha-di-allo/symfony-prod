<?php
// // src/EventSubscriber/CheckLoggedInSubscriber.php

// namespace App\EventSubscriber;

// use Symfony\Component\EventDispatcher\EventSubscriberInterface;
// use Symfony\Component\HttpKernel\Event\RequestEvent;
// use Symfony\Component\HttpFoundation\RedirectResponse;
// use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
// use Symfony\Component\HttpFoundation\Session\SessionInterface;
// use Symfony\Component\HttpFoundation\RequestStack;
// use Symfony\Component\Security\Core\Security;

// class CheckLoggedInSubscriber implements EventSubscriberInterface
// {
//     private $urlGenerator;
//     private $session;
//     private $requestStack;
//     private $security;

//     public function __construct(UrlGeneratorInterface $urlGenerator, SessionInterface $session, RequestStack $requestStack, Security $security)
//     {
//         $this->urlGenerator = $urlGenerator;
//         $this->session = $session;
//         $this->requestStack = $requestStack;
//         $this->security = $security;
//     }

//     public function onKernelRequest(RequestEvent $event)
//     {
//         $request = $event->getRequest();
//         $routeName = $request->attributes->get('_route');

//         // Vérifier si l'utilisateur est connecté et si la route est liée aux messages
//         if (!$this->isUserLoggedIn() && $this->isMessageRoute($routeName)) {
//             // Ajouter un message flash
//             $this->session->set('_flash', ['notice' => 'Veuillez vous inscrire et vous connecter pour accéder à vos messages.']);

//             // Rediriger l'utilisateur vers une autre page
//             $response = new RedirectResponse($this->urlGenerator->generate('app_login'));
//             $event->setResponse($response);
//         }
//     }

//     private function isUserLoggedIn()
//     {
//         // Vérifiez si l'utilisateur est connecté en utilisant Security
//         return $this->security->getUser() !== null;
//     }

//     private function isMessageRoute($routeName)
//     {
//         // Liste des noms de route liés aux messages
//         $messageRoutes = ['app_send', 'app_received', 'app_sent' , 'app_read']; // Ajoutez ici les noms de vos routes

//         return in_array($routeName, $messageRoutes);
//     }

//     public static function getSubscribedEvents()
//     {
//         return [
//             RequestEvent::class => 'onKernelRequest',
//         ];
//     }
// }
