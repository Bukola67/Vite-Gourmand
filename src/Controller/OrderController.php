<?php

namespace App\Controller;

use App\Entity\CustomerOrder;
use App\Entity\Menu;
use App\Entity\OrderStatusHistory;
use App\Form\CustomerOrderType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/commande', name: 'app_order_')]
final class OrderController extends AbstractController
{
    #[Route('/new/{id}', name: 'new', methods: ['GET', 'POST'])]
    public function new(
        Menu $menu,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {        
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour commander.');
        }

        $order = new CustomerOrder();

        $order->setCustomerFirstName($user->getFirstName());
        $order->setCustomerLastName($user->getLastName());
        $order->setCustomerEmail($user->getEmail());
        $order->setCustomerPhone($user->getPhone());

        $order->setDeliveryAddress($user->getAddress());
        $order->setDeliveryPostalCode($user->getPostalCode());
        $order->setDeliveryCity($user->getCity());

        $form = $this->createForm(CustomerOrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
                $personCount = $order->getPersonCount();
                $minimumPersons = $menu->getMinimumPersons();
                $basePrice = (float) $menu->getBasePrice();

                if ($personCount < $minimumPersons) {
                    $this->addFlash('danger', 'Le nombre minimum de personnes pour ce menu est de ' . $minimumPersons . '.');

                    return $this->render('order/new.html.twig', [
                        'orderForm' => $form->createView(),
                        'menu' => $menu,
                    ]);
                }

                $menuPrice = $basePrice;
                $deliveryPrice = 0.00;
                $discountAmount = 0.00;

                if (mb_strtolower($order->getDeliveryCity()) !== 'bordeaux') {
                    $deliveryPrice = 5.00;
                }

                if ($personCount >= $minimumPersons + 5) {
                    $discountAmount = $menuPrice * 0.10;
                }

                $totalPrice = $menuPrice + $deliveryPrice - $discountAmount;

            $order->setUser($user);
            $order->setMenu($menu);
            $order->setStatus('en_attente');
            $order->setCreatedAt(new \DateTimeImmutable());

            $order->setMenuPrice(number_format($menuPrice, 2, '.', ''));
            $order->setDeliveryPrice(number_format($deliveryPrice, 2, '.', ''));
            $order->setDiscountAmount(number_format($discountAmount, 2, '.', ''));
            $order->setTotalPrice(number_format($totalPrice, 2, '.', ''));

            $entityManager->persist($order);

            $history = new OrderStatusHistory();
            $history->setStatus($order->getStatus());
            $history->setChangedAt(new \DateTimeImmutable());
            $history->setCustomerOrder($order);

            $entityManager->persist($history);
            $entityManager->flush();

            $this->addFlash('success', 'Votre commande a bien été enregistrée.');

            return $this->redirectToRoute('app_user_space');
        }

        return $this->render('order/new.html.twig', [
            'orderForm' => $form->createView(),
            'menu' => $menu,
        ]);
    }
}