<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User; // Import de l'entité User
use Faker\Factory as FakerFactory; // Import de Faker
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture // Renommage de la classe
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void // Correctement typée
    {
        $faker = FakerFactory::create('fr_FR');

        for ($i = 0; $i < 10; $i++) {
            $user = new User();
            $user->setEmail($faker->email);
            $user->setPassword($this->passwordHasher->hashPassword($user, 'password123'));
            $user->setRoles(['ROLE_USER']);
            $user->setMatricule($faker->unique()->numerify('EMP####'));

            $manager->persist($user);
        }

        // Ajouter d'autres entités selon vos besoins
        $manager->flush();
    }
}
