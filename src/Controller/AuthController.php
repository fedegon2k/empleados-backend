<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class AuthController extends AbstractController
{
    #[Route('/api/register', name: 'register', methods: ['POST'])]
    public function register(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
    
        if (!$data || !isset($data['email']) || !isset($data['password'])) {
            return new JsonResponse(['message' => 'Datos inválidos'], JsonResponse::HTTP_BAD_REQUEST);
        }
    
        $user = new User();
        $user->setEmail($data['email']);
        $hashedPassword = $passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($hashedPassword);
        $user->setRoles(['ROLE_ADMIN']); // se supone que aquí solo acceden los ROLE_ADMIN
    
        $entityManager->persist($user);
        $entityManager->flush();
    
        return new JsonResponse(['message' => 'Usuario registrado exitosamente'], JsonResponse::HTTP_CREATED);
    }

    #[Route('/api/login', name: 'login', methods: ['POST'])]
    public function login(Request $request, JWTManager $jwtManager, AuthenticationUtils $authenticationUtils): JsonResponse
    {
        // Intenta obtener el error de autenticación y el nombre de usuario
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        if ($error) {
            return new JsonResponse(['message' => 'Credenciales inválidas'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        // Si no hay error, obtenemos el usuario y creamos el JWT
        $user = $this->getUser(); // Obtiene el usuario autenticado
        $token = $jwtManager->create($user);

        return new JsonResponse(['token' => $token]);
    }

    #[Route('/api/profile', name: 'profile', methods: ['GET'])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function profile(): JsonResponse
    {
        return $this->json([
            'email' => $this->getUser()->getUserIdentifier(),
            'roles' => $this->getUser()->getRoles(),
        ]);
    }
}
