<?php

namespace App\Tests\Functional;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserLoginTest extends WebTestCase
{
    public function testUserLogin()
    {
        $client = static::createClient();
        $container = $client->getContainer();

        // Créez un utilisateur de test
        $user = new User();
        $user->setEmail('test@example.com');
        $plainPassword = 'password123';

        // Hachez le mot de passe
        $passwordHasher = $container->get(UserPasswordHasherInterface::class);
        $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);

        // Persistez l'utilisateur dans la base de données de test
        $entityManager = $container->get('doctrine')->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        // Simulez une connexion
        $client->request('POST', '/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        // Vérifiez que la connexion a réussi
        $this->assertTrue($client->getResponse()->isRedirect());

        // Vérifiez que le mot de passe haché correspond au mot de passe en clair
        $this->assertTrue($passwordHasher->isPasswordValid($user, $plainPassword));

        // Vérifiez que le mot de passe haché ne correspond pas à un mauvais mot de passe
        $this->assertFalse($passwordHasher->isPasswordValid($user, 'wrongpassword'));
    }
}