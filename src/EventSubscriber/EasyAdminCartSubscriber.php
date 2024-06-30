<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Entity\Panier;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EasyAdminCartSubscriber implements EventSubscriberInterface
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeEntityPersistedEvent::class => ['addPanierToUser'],
        ];
    }

    public function employeLessUser(EntityManagerInterface $entityManager)
    {
        $entityManager->getRepository(User::class);

    }

    public function addPanierToUser(BeforeEntityPersistedEvent $event): void
    {
        $entity = $event->getEntityInstance();

        if ($entity instanceof User) {
            // Créer un nouveau panier
            $panier = new Panier();

            // Associer l'utilisateur au panier
            $panier->setUser($entity);

            // Persistez les changements dans l'EntityManager
            $this->entityManager->persist($panier);
        }
    }
}
