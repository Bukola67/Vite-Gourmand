<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\EmployeeType;
use Psr\Log\LoggerInterface;
use App\Repository\UserRepository;
use App\Repository\MenuRepository;
use App\Service\MongoStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


#[Route('/admin', name: 'app_admin_')]
#[IsGranted('ROLE_ADMIN')]
class AdminSpaceController extends AbstractController
{
    #[Route('', name: 'index')]
    public function index(UserRepository $userRepository): Response
    {
        $users = $userRepository->findBy([], ['createdAt' => 'DESC']);

        $employees = array_filter($users, function (User $user) {
            return in_array('ROLE_EMPLOYEE', $user->getRoles(), true);
        });

        return $this->render('admin_space/index.html.twig', [
            'employees' => $employees,
        ]);
    }

    #[Route('/employes/new', name: 'employee_new')]
    public function newEmployee(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $employee = new User();
        $employee->setRoles(['ROLE_EMPLOYEE']);
        $employee->setIsActive(true);
        $employee->setCreatedAt(new \DateTimeImmutable());

        $form = $this->createForm(EmployeeType::class, $employee);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();

            $hashedPassword = $passwordHasher->hashPassword($employee, $plainPassword);
            $employee->setPassword($hashedPassword);

            $entityManager->persist($employee);
            $entityManager->flush();

            $this->addFlash('success', 'Le compte employé a bien été créé.');

            return $this->redirectToRoute('app_admin_index');
        }

        return $this->render('admin_space/new_employee.html.twig', [
            'employeeForm' => $form->createView(),
        ]);
    }

    #[Route('/employes/{id}/toggle-active', name: 'employee_toggle_active', methods: ['POST'])]
    public function toggleEmployeeActive(
        User $employee,
        Request $request,
        EntityManagerInterface $entityManager
    ): RedirectResponse {
        if (!$this->isCsrfTokenValid('toggle_employee_' . $employee->getId(), $request->request->get('_token'))) {
            $this->addFlash('danger', 'Jeton CSRF invalide.');
            return $this->redirectToRoute('app_admin_index');
        }

        if (!in_array('ROLE_EMPLOYEE', $employee->getRoles(), true)) {
            $this->addFlash('danger', 'Cet utilisateur n’est pas un employé.');
            return $this->redirectToRoute('app_admin_index');
        }

        if ($this->getUser() === $employee) {
            $this->addFlash('danger', 'Vous ne pouvez pas désactiver votre propre compte.');
            return $this->redirectToRoute('app_admin_index');
        }

        $employee->setIsActive(!$employee->isActive());
        $entityManager->flush();

        $message = $employee->isActive()
            ? 'Le compte employé a été activé.'
            : 'Le compte employé a été désactivé.';

        $this->addFlash('success', $message);

        return $this->redirectToRoute('app_admin_index');
    }

    #[Route('/stats', name: 'stats', methods: ['GET'])]
    public function stats(
        Request $request,
        MongoStatsService $mongoStatsService,
        MenuRepository $menuRepository,
        LoggerInterface $logger,
    ): Response {
        $query = $request->query->all();
        $errors = [];
        $values = [];
        foreach (['menuId', 'startMonth', 'endMonth'] as $name) {
            $raw = $query[$name] ?? '';
            if (!is_string($raw)) {
                $errors[$name] = 'Une valeur simple est attendue.';
                $values[$name] = '';
                continue;
            }
            $values[$name] = trim($raw);
        }

        $menuId = null;
        if ($values['menuId'] !== '' && !isset($errors['menuId'])) {
            $menuId = filter_var($values['menuId'], FILTER_VALIDATE_INT, [
                'options' => ['min_range' => 1, 'max_range' => 2147483647],
            ]);
            if ($menuId === false) {
                $errors['menuId'] = 'Le menu doit être identifié par un entier positif.';
                $menuId = null;
            } elseif ($menuRepository->find($menuId) === null) {
                $errors['menuId'] = 'Ce menu n’existe pas.';
            }
        }

        foreach (['startMonth', 'endMonth'] as $name) {
            if ($values[$name] !== '' && !isset($errors[$name])
                && !preg_match('/^[1-9][0-9]{3}-(0[1-9]|1[0-2])$/D', $values[$name])) {
                $errors[$name] = 'Un mois au format AAAA-MM est attendu (année de 1000 à 9999).';
            }
        }
        if ($errors === [] && $values['startMonth'] !== '' && $values['endMonth'] !== ''
            && $values['startMonth'] > $values['endMonth']) {
            $errors['endMonth'] = 'La fin doit être postérieure ou égale au début.';
        }

        $stats = [];
        $unavailable = false;
        if ($errors === []) {
            try {
                $stats = $mongoStatsService->getRevenueByMenuAndPeriod(
                    $menuId,
                    $values['startMonth'] !== '' ? $values['startMonth'] : null,
                    $values['endMonth'] !== '' ? $values['endMonth'] : null,
                );
            } catch (\Exception $exception) {
                $unavailable = true;
                $logger->error('Lecture des statistiques impossible.', ['exception' => $exception]);
            }
        }

        return $this->render('admin_space/stats.html.twig', [
            'stats' => $stats,
            'menus' => $menuRepository->findBy([], ['title' => 'ASC']),
            'selectedMenuId' => $values['menuId'],
            'startMonth' => $values['startMonth'],
            'endMonth' => $values['endMonth'],
            'filterErrors' => $errors,
            'statsUnavailable' => $unavailable,
        ], new Response(status: $errors !== [] ? Response::HTTP_BAD_REQUEST
            : ($unavailable ? Response::HTTP_SERVICE_UNAVAILABLE : Response::HTTP_OK)));
    }
}
