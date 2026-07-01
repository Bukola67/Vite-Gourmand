<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\EmployeeType;
use App\Repository\UserRepository;
use App\Repository\MenuRepository;
use App\Service\MongoStatsService ;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
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
        $employees = $userRepository->findBy([], ['createdAt' => 'DESC']);

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

    #[Route('/stats', name: 'stats')]
    public function stats(
        Request $request,
        MongoStatsService $mongoStatsService,
        MenuRepository $menuRepository
    ): Response {
        $menuId = $request->query->get('menuId');
        $startMonth = $request->query->get('startMonth');
        $endMonth = $request->query->get('endMonth');

        $chartStats = $mongoStatsService->getMenuStats();
        $revenueStats = $mongoStatsService->getRevenueByMenuAndPeriod(
            $menuId ? (int) $menuId : null,
            $startMonth ?: null,
            $endMonth ?: null
        );

        return $this->render('admin_space/stats.html.twig', [
            'chartStats' => $chartStats,
            'revenueStats' => $revenueStats,
            'menus' => $menuRepository->findAll(),
            'selectedMenuId' => $menuId,
            'startMonth' => $startMonth,
            'endMonth' => $endMonth,
        ]);
     }
     
    }