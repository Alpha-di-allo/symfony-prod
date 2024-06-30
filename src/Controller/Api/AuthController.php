<?php

namespace App\Controller\Api;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface; // Pour Symfony 5.x et supérieur
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface; // Pour Symfony 4.x et inférieur



class AuthController extends AbstractController
{
    private $userRepository;
    private $passwordHasher;
    private $JWTManager;

    // Utilisez UserPasswordEncoderInterface pour Symfony 4.x et inférieur
    // Utilisez UserPasswordHasherInterface pour Symfony 5.x et supérieur


    public function __construct(UserRepository $userRepository,  UserPasswordHasherInterface $passwordHasher, JWTTokenManagerInterface $JWTManager)
    {
        $this->userRepository = $userRepository;
        $this->passwordHasher = $passwordHasher;
        $this->JWTManager = $JWTManager;
    }


    #[Route('Api/login-check',name : 'app_login_check', methods: ["POST"])]
    public function loginCheck(Request $request):JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['email']) || !isset($data['password'])) {
            return new JsonResponse(['error' => 'Invalid credentials'], 400);
        }

        $email = $data['email'];
        $plainPassword = $data['password'];

        try {
            $user = $this->userRepository->findOneBy(['email' => $email]);

            if (!$user || !$this->passwordHasher->isPasswordValid($user, $plainPassword)) {
                return new JsonResponse(['error' => 'Identifiant Invalide'], 401);
            }

            $token = $this->JWTManager->create($user);


            return new JsonResponse([
                'token' => $token,
            ], 200);
        } catch (AuthenticationException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

   

}
