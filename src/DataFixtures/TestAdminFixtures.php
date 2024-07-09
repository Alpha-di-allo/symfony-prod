<?php
// src/DataFixtures/TestAdminFixtures.php
namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class TestAdminFixtures extends Fixture
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager)
    {
        $admin = new User();
        $admin->setEmail('admin@example.com');
        $admin->setFirstname('Admin');
        $admin->setLastname('User');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setNumeroTel("0653495676");
        $admin->setAdresse("rue des boss");
        $admin->setCodePostal("93200");
        $admin->setVille("saint Denis");
        $admin->setMatricule("ec841");
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'adminpassword'));
        $manager->persist($admin);
        $manager->flush();
    }
}