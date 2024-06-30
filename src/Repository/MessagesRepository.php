<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Messages;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Messages>
 *
 * @method Messages|null find($id, $lockMode = null, $lockVersion = null)
 * @method Messages|null findOneBy(array $criteria, array $orderBy = null)
 * @method Messages[]    findAll()
 * @method Messages[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MessagesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Messages::class);
    }

//    /**
//     * @return Messages[] Returns an array of Messages objects
//     */

public function findByUser($user) // Récuperer les messages de l'utilisateur connecté
{
    return $this->createQueryBuilder('m')
        ->andWhere('m.sender = :user')
        ->setParameter('user', $user)
        ->orderBy('m.createdAt', 'DESC')
        ->getQuery()
        ->getResult();
}


public function findReceivedMessagesByUser(User $user) // Messages recu par l'utilisateur 
{
    return $this->createQueryBuilder('m')
        ->andWhere('m.recipient = :user')
        // ->andWhere('m.isDeletedByRecipient = false')
        ->setParameter('user', $user)
        ->orderBy('m.createdAt', 'DESC')
        ->getQuery()
        ->getResult();
}

public function findSentMessagesByUser(User $user) // Messages envoyer par l'utilisateur 
{
    return $this->createQueryBuilder('m')
        ->andWhere('m.sender = :user')
        // ->andWhere('m.isDeletedBySender = false')
        ->setParameter('user', $user)
        ->orderBy('m.createdAt', 'DESC')
        ->getQuery()
        ->getResult();
}

public function findContactsForUser(User $user)   // Trouver les destinataires  de l'utilisateur 
{
    $messages = $this->findBy(['recipient' => $user]);

    $senders = [];
    foreach ($messages as $message) {
        $sender = $message->getSender();
        // Utilisation de  l'ID de l'utilisateur comme clé dans le tableau associatif
        // pour garantir l'unicité des expéditeurs
        $senders[$sender->getId()] = $sender;
    }
      // Retourner les expéditeurs uniques en extrayant les valeurs du tableau associatif
      return array_values($senders);
}


public function findUniqueSendersForUser(User $user)
{
    $qb = $this->createQueryBuilder('m')
        ->leftJoin('m.sender', 's')
        ->where('m.recipient = :user')
        ->setParameter('user', $user)
        ->select('m') // Sélectionne les messages 
        ->distinct(true)
        ->getQuery();

    $messages = $qb->getResult();
    $senders = [];
    foreach ($messages as $message) {
        $sender = $message->getSender();
        // Assurez-vous de ne pas ajouter le même expéditeur plusieurs fois
        if (!array_key_exists($sender->getId(), $senders)) {
            $senders[$sender->getId()] = $sender;
        }
    }

    return array_values($senders); // Retourne les expéditeurs uniques
}

public function findReceivedMessagesByUserFromAdvisor(User $user)
{
    return $this->createQueryBuilder('m')
        ->andWhere('m.recipient = :user')
        ->andWhere('m.sender = :advisor') // Ajoutez cette ligne
        ->andWhere('m.isDeletedByRecipient = false')
        ->setParameter('user', $user)
        ->setParameter('advisor', $user->getEmployer()) // Définissez le conseiller comme paramètre
        ->orderBy('m.createdAt', 'DESC')
        ->getQuery()
        ->getResult();
}

//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('m.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Messages
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
