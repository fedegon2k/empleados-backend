<?php

namespace App\Controller;

use App\Entity\Employee;
use App\Entity\User;
use App\Repository\EmployeeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Service\MailerService;

final class EmployeeController extends AbstractController
{
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     *  Obtener todos los empleados o buscarlos por nombre
     */
    #[Route('/api/employees/', name: 'employee_list', methods: ['GET'])]
    public function list(Request $request, EmployeeRepository $employeeRepository): JsonResponse
    {
        $search = $request->query->get('search');
    
        if ($search) {
            $employees = $employeeRepository->createQueryBuilder('e')
                ->where('e.firstName LIKE :search OR e.lastName LIKE :search')
                ->setParameter('search', '%' . $search . '%')
                ->getQuery()
                ->getResult();
        } else {
            $employees = $employeeRepository->findAll();
        }
    
        $data = array_map(fn ($employee) => [
            'id' => $employee->getId(),
            'firstName' => $employee->getFirstName(),
            'lastName' => $employee->getLastName(),
            'email' => $employee->getUser()?->getEmail(),
            'position' => $employee->getPosition(),
            'birthDate' => $employee->getBirthDate()?->format('Y-m-d'),
        ], $employees);
    
        return $this->json($data);
    }

    /**
     *  Crear un nuevo empleado
     */
    #[Route('/api/employees/', name: 'employee_create', methods: ['POST'])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function create(Request $request, EntityManagerInterface $entityManager, MailerService $mailerService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['firstName'], $data['lastName'], $data['position'], $data['birthDate'], $data['email'], $data['password'])) {
            return new JsonResponse(['error' => 'Datos inválidos'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Crear un nuevo usuario para el empleado
        $user = new User();
        $user->setEmail($data['email']);
        $user->setPassword(password_hash($data['password'], PASSWORD_BCRYPT));
        $user->setRoles(['ROLE_EMPLOYEE']);  // rol de empleado

        // Asociar el usuario con el empleado
        $employee = new Employee();
        $employee->setFirstName($data['firstName']);
        $employee->setLastName($data['lastName']);
        $employee->setPosition($data['position']);
        $employee->setBirthDate(new \DateTime($data['birthDate']));
        $employee->setUser($user); // Asociamos el usuario al empleado

        $entityManager->persist($user); // Guardar el usuario
        $entityManager->persist($employee); // Guardar el empleado
        $entityManager->flush();

        // Enviar correo de bienvenida
        $mailerService->sendEmail(
        $employee->getEmail(),
        'Bienvenido a la empresa',
        'emails/welcome_email.html.twig',
        [
            'name' => $employee->getFirstName() . " " . $employee->getLastName(),
            'position' => $employee->getPosition(),
        ]
    );

        return $this->json(['message' => 'Empleado creado exitosamente'], JsonResponse::HTTP_CREATED);
    }

    /**
     *  Actualizar nombres si es admin o cargo si es el mismo empleado 
     */
    #[Route('/api/employees/{id}', name: 'employee_update', methods: ['PUT'])]
    public function update(Request $request, Employee $employee, EntityManagerInterface $entityManager, UserInterface $currentUser): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
    
        // Verificar si el usuario autenticado es ADMIN
        if (in_array('ROLE_ADMIN', $currentUser->getRoles())) {
            if (isset($data['firstName'])) {
                $employee->setFirstName($data['firstName']);
            }
            if (isset($data['lastName'])) {
                $employee->setLastName($data['lastName']);
            }
            if (isset($data['position'])) {
                $employee->setPosition($data['position']);
            }
        } 
        // Si el usuario es un empleado, solo puede cambiar su propia posición
        elseif ($employee->getUser() === $currentUser) {
            if (isset($data['position'])) {
                $employee->setPosition($data['position']);
            } else {
                return new JsonResponse(['error' => 'Solo puedes modificar tu cargo.'], JsonResponse::HTTP_FORBIDDEN);
            }
        } 
        else {
            return new JsonResponse(['error' => 'No tienes permiso para editar este empleado.'], JsonResponse::HTTP_FORBIDDEN);
        }
    
        $entityManager->flush();
    
        return $this->json(['message' => 'Empleado actualizado exitosamente']);
    }    

    /**
     *  Eliminar un empleado (solo puede hacerlo el empleado mismo)
     */
    #[Route('/api/employees/{id}', name: 'employee_delete', methods: ['DELETE'])]
    public function delete(Employee $employee, EntityManagerInterface $entityManager, UserInterface $currentUser): JsonResponse
    {
        // Verificar si el empleado es el mismo que el usuario autenticado
        if ($employee->getUser() !== $currentUser) {
            return new JsonResponse(['error' => 'No tienes permiso para eliminar este empleado.'], JsonResponse::HTTP_FORBIDDEN);
        }

        $entityManager->remove($employee);
        $entityManager->flush();

        return $this->json(['message' => 'Empleado eliminado exitosamente']);
    }

    /**
     *  Obtener posiciones de la API externa
     */
    #[Route('/api/employees/positions', name: 'employee_positions', methods: ['GET'])]
    public function getPositions(): JsonResponse
    {
        $response = $this->httpClient->request('GET', 'https://ibillboard.com/api/positions');
        $positions = $response->toArray();

        return $this->json($positions);
    }

}
