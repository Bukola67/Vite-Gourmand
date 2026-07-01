<?php

namespace App\Controller;

use App\Entity\CustomerOrder;
use App\Entity\OrderStatusHistory;
use App\Repository\CustomerOrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/employe', name: 'app_employee_')]
#[IsGranted(new \Symfony\Component\ExpressionLanguage\Expression(
    "is_granted('ROLE_EMPLOYEE') or is_granted('ROLE_ADMIN')"
))]
class EmployeeSpaceController extends AbstractController
{
    #[Route('/commandes', name: 'orders')]
    public function orders(Request $request, CustomerOrderRepository $customerOrderRepository): Response
    {
        $status = $request->query->get('status');

        if ($status) {
            $orders = $customerOrderRepository->findBy(
                ['status' => $status],
                ['createdAt' => 'DESC']
            );
        } else {
            $orders = $customerOrderRepository->findBy([], ['createdAt' => 'DESC']);
        }

        return $this->render('employee_space/orders.html.twig', [
            'orders' => $orders,
            'selectedStatus' => $status,
        ]);
    }

    #[Route('/commandes/{id}', name: 'order_show')]
    public function show(CustomerOrder $order): Response
    {
        return $this->render('employee_space/show.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('/commandes/{id}/statut', name: 'order_status_update', methods: ['POST'])]
    public function updateStatus(
        Request $request,
        CustomerOrder $order,
        EntityManagerInterface $entityManager
    ): Response {
        if (!$this->isCsrfTokenValid('update_status_' . $order->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        $newStatus = $request->request->get('status');

        $allowedStatuses = [
            'en_attente',
            'acceptee',
            'en_preparation',
            'en_livraison',
            'livree',
            'terminee',
            'annulee',
        ];

        if (!in_array($newStatus, $allowedStatuses, true)) {
            $this->addFlash('danger', 'Statut invalide.');
            return $this->redirectToRoute('app_employee_order_show', ['id' => $order->getId()]);
        }

        $order->setStatus($newStatus);
        $order->setUpdatedAt(new \DateTimeImmutable());

        $history = new OrderStatusHistory();
        $history->setStatus($newStatus);
        $history->setChangedAt(new \DateTimeImmutable());
        $history->setCustomerOrder($order);

        $entityManager->persist($history);
        $entityManager->flush();

        $this->addFlash('success', 'Le statut de la commande a bien été mis à jour.');

        return $this->redirectToRoute('app_employee_order_show', ['id' => $order->getId()]);
    }
}