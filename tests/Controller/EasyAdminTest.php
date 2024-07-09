<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class EasyAdminTest extends WebTestCase
{
    protected $client;
    protected $entityManager;
    protected $passwordHasher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->passwordHasher = self::getContainer()->get(UserPasswordHasherInterface::class);
        $this->createAdminUser();
        $this->loginAsAdmin();
    }

    private function createAdminUser()
    {
        $user = new User();
        $user->setEmail('admin2@example.com');
        $user->setFirstname('Admin');
        $user->setLastname('User');
        $user->setRoles(['ROLE_ADMIN']);
        $user->setVille('NOISY');
        $user->setCodePostal('93000');
        $user->setAdresse('rue des test');
        $user->setMatricule('ZE403');
        $user->setNumeroTel('0910304965');

        $hashedPassword = $this->passwordHasher->hashPassword($user, 'password123');
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    private function loginAsAdmin()
    {
        $userRepository = $this->entityManager->getRepository(User::class);
        $testUser = $userRepository->findOneByEmail('admin@example.com');
        $this->client->loginUser($testUser);
    }

    public function testAdminDashboardAccess()
    {
        $crawler = $this->client->request('GET', '/admin');
        $this->assertResponseIsSuccessful();
        // Vérifiez simplement que la page contient un élément qui indique qu'il s'agit du tableau de bord admin
        $this->assertSelectorExists('.ea-content-header');
    }

    public function testUserCRUD()
    {
        // Test de création d'utilisateur
        $crawler = $this->client->request('GET', '/admin');
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        // Trouver le lien pour créer un nouvel utilisateur et le suivre
        $linkText = 'Add user ';
        $link = $crawler->selectLink($linkText)->link();
        $crawler = $this->client->click($link);

        $this->assertResponseIsSuccessful();

        // Remplir et soumettre le formulaire
        $form = $crawler->selectButton('Create')->form();
        $form['User[email]'] = 'test@example.com';
        $form['User[firstname]'] = 'Test';
        $form['User[lastname]'] = 'User';
        $form['User[numeroTel]'] = '0714265672';
        $form['User[adresse]'] = 'rue du plateau';
        $form['User[ville]'] = 'Champigny';
        $form['User[codePostal]'] = '94500';
        $form['User[plainPassword]'] = 'password123';

        $this->client->submit($form);

        // Vérifier la redirection
        $this->assertResponseRedirects();

        // Suivre la redirection
        $crawler = $this->client->followRedirect();

        // Vérifier que le nouvel utilisateur apparaît dans la liste
        $this->assertSelectorTextContains('body', 'test@example.com');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        // On ne supprime pas l'admin pour éviter les problèmes avec d'autres tests potentiels
        $this->entityManager->createQuery('DELETE FROM App\Entity\User u WHERE u.email != :email')
            ->setParameter('email', 'admin@example.com')
            ->execute();
    }
}