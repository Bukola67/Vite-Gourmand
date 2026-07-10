<?php

namespace App\Controller;

use App\Entity\CustomerOrder;
use App\Entity\User;
use App\Entity\Review;
use App\Form\ReviewType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class UserSpaceController extends AbstractController
{
    #[Route('/compte', name: 'app_user_space')]
    #[IsGranted('ROLE_USER')]
    public function index(): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('user_space/index.html.twig', [
            'orders' => $user->getCustomerOrders(),
        ]);
    }

    #[Route('/compte/commande/{id}', name: 'app_user_order_show')]
    #[IsGranted('ROLE_USER')]
    public function show(CustomerOrder $order): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($order->getUser() !== $user) {
            throw $this->createAccessDeniedException('Accès refusé à cette commande.');
        }

        return $this->render('user_space/show.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('/compte/commande/{id}/annuler', name: 'app_user_order_cancel', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function cancel(Request $request, CustomerOrder $order, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($order->getUser() !== $user) {
            throw $this->createAccessDeniedException('Accès refusé à cette commande.');
        }

        if ($order->getStatus() === 'acceptee') {
            $this->addFlash('danger', 'Cette commande ne peut plus être annulée.');
            return $this->redirectToRoute('app_user_order_show', ['id' => $order->getId()]);
        }

        if ($this->isCsrfTokenValid('cancel_order_'.$order->getId(), $request->request->get('_token'))) {
            $order->setStatus('annulee');
            $entityManager->flush();

            $this->addFlash('success', 'La commande a bien été annulée.');
        }

        return $this->redirectToRoute('app_user_space');
    }


    #[Route('/compte/commande/{id}/avis', name: 'app_user_order_review')]
    #[IsGranted('ROLE_USER')]
    public function review(
        Request $request,
        CustomerOrder $order,
        EntityManagerInterface $entityManager
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        if ($order->getUser() !== $user) {
            throw $this->createAccessDeniedException('Accès refusé à cette commande.');
        }

        if ($order->getStatus() !== 'terminee') {
            $this->addFlash('danger', 'Vous ne pouvez laisser un avis que pour une commande terminée.');
            return $this->redirectToRoute('app_user_order_show', ['id' => $order->getId()]);
        }

        $existingReview = $entityManager->getRepository(Review::class)->findOneBy([
            'user' => $user,
        ]);

        if ($existingReview) {
            $this->addFlash('warning', 'Vous avez déjà laissé un avis.');
            return $this->redirectToRoute('app_user_order_show', ['id' => $order->getId()]);
        }

        $review = new Review();
        $review->setUser($user);
        $review->setCreatedAt(new \DateTimeImmutable());
        $review->setIsValidated(false);

        $form = $this->createForm(ReviewType::class, $review);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($review);
            $entityManager->flush();

            $this->addFlash('success', 'Votre avis a bien été envoyé. Il sera visible après validation.');
            return $this->redirectToRoute('app_user_order_show', ['id' => $order->getId()]);
        }

        return $this->render('user_space/review.html.twig', [
            'order' => $order,
            'form' => $form->createView(),
        ]);
    }

}

