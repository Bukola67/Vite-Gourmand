<?php

namespace App\Controller;

use App\Service\MailService;
use App\Entity\Review;
use App\Entity\CustomerOrder;
use App\Entity\OrderStatusHistory;
use App\Repository\CustomerOrderRepository;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
        EntityManagerInterface $entityManager,
        MailService $mailService
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
            'en_attente_retour_materiel',
            'terminee',
            'annulee',
        ];

        if (!in_array($newStatus, $allowedStatuses, true)) {
            $this->addFlash('danger', 'Statut invalide.');
            return $this->redirectToRoute('app_employee_order_show', ['id' => $order->getId()]);
        }

        $oldStatus = $order->getStatus();

        $order->setStatus($newStatus);
        $order->setUpdatedAt(new \DateTimeImmutable());

        $history = new OrderStatusHistory();
        $history->setStatus($newStatus);
        $history->setChangedAt(new \DateTimeImmutable());
        $history->setCustomerOrder($order);

        $entityManager->persist($history);

        if ($newStatus === 'en_attente_retour_materiel') {
        $mailService->sendEquipmentReturnReminder($order);
        }

        $entityManager->flush();

        $this->addFlash('success', 'Le statut de la commande a bien été mis à jour.');

        return $this->redirectToRoute('app_employee_order_show', ['id' => $order->getId()]);
    }
    #[Route('/avis', name: 'reviews')]
    public function reviews(ReviewRepository $reviewRepository): Response
    {
        return $this->render('employee_space/reviews.html.twig', [
            'pendingReviews' => $reviewRepository->findPendingReviews(),
            'validatedReviews' => $reviewRepository->findValidatedReviews(),
        ]);
    }

    #[Route('/avis/{id}/valider', name: 'review_validate', methods: ['POST'])]
    public function validateReview(
        Review $review,
        Request $request,
        EntityManagerInterface $entityManager
    ): RedirectResponse {
        if (!$this->isCsrfTokenValid('validate_review_' . $review->getId(), $request->request->get('_token'))) {
            $this->addFlash('danger', 'Jeton CSRF invalide.');
            return $this->redirectToRoute('app_employee_reviews');
        }

        $review->setIsValidated(true);
        $entityManager->flush();

        $this->addFlash('success', 'L’avis a été validé.');

        return $this->redirectToRoute('app_employee_reviews');
    }

    #[Route('/avis/{id}/refuser', name: 'review_refuse', methods: ['POST'])]
    public function refuseReview(
        Review $review,
        Request $request,
        EntityManagerInterface $entityManager
    ): RedirectResponse {
        if (!$this->isCsrfTokenValid('refuse_review_' . $review->getId(), $request->request->get('_token'))) {
            $this->addFlash('danger', 'Jeton CSRF invalide.');
            return $this->redirectToRoute('app_employee_reviews');
        }

        $entityManager->remove($review);
        $entityManager->flush();

        $this->addFlash('success', 'L’avis a été refusé.');

        return $this->redirectToRoute('app_employee_reviews');
    }

    
}