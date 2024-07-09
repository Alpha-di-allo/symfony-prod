<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SecurityControllerTest extends WebTestCase
{
    private $entityManager;
    private $client;
    private $passwordHasher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $this->entityManager = self::getContainer()->get('doctrine')->getManager();
        $this->passwordHasher = self::getContainer()->get(UserPasswordHasherInterface::class);
    }

    public function testLogin()
    {
        // Créer un utilisateur de test
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setFirstname('Test');
        $user->setLastname('User');
        $hashedPassword = $this->passwordHasher->hashPassword($user, 'password123');
        $user->setPassword($hashedPassword);
        $user->setRoles(['ROLE_ADMIN']);
        $user->setVille('NOISY');
        $user->setCodePostal('93000');
        $user->setAdresse('rue des test');
        $user->setMatricule('ZE403');
        $user->setNumeroTel('0910304965');

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // Accéder à la page de connexion
        $crawler = $this->client->request('GET', '/login');
        $this->assertResponseIsSuccessful();

        // Remplir et soumettre le formulaire de connexion
        $form = $crawler->filter('form[action="/login"]')->form();
        $form['_username'] = 'test@example.com';
        $form['_password'] = 'password123';

        $this->client->submit($form);

        // Vérifier la redirection
        $this->assertResponseStatusCodeSame(302);
        $this->assertResponseHasHeader('Location');
        
        // Suivre la redirection
        $crawler = $this->client->followRedirect();

        // Vérifier que nous sommes bien sur la page d'accueil
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2.title', 'VENTALIS BURGER :');
        $this->assertSelectorTextContains('h3.subtitle', 'LE MYTHIQUE SMASH BURGER ARRIVE CHEZ VOUS');

        // Vérifier la présence d'éléments spécifiques à la page d'accueil
        $this->assertSelectorExists('.video-container');
        $this->assertSelectorExists('.grid-container');
        $this->assertSelectorExists('.info-container');
        $this->assertSelectorExists('.gallery-container');
        $this->assertSelectorExists('a.discover-button');

        // Vérifier que le bouton "Découvrez-nos Produits" est présent
        $this->assertSelectorTextContains('a.discover-button', 'Découvrez-nos Produits');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Nettoyer la base de données après le test
        $this->entityManager->createQuery('DELETE FROM App\Entity\User')->execute();
        $this->entityManager->close();
        $this->entityManager = null;
    }
}
