<?php

namespace App\Controller;

use App\Entity\CustomerOrder;
use App\Entity\Menu;
use App\Entity\OrderStatusHistory;
use App\Form\CustomerOrderType;
use App\Service\MongoStatsService;
use App\Service\OrderPriceCalculator;
use App\Service\DeliveryDistanceService;
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
            EntityManagerInterface $entityManager,
            MongoStatsService $mongoStatsService,
            OrderPriceCalculator $priceCalculator,
            DeliveryDistanceService $deliveryDistanceService,
            \App\Service\DeliveryFeeCalculator $deliveryFeeCalculator
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

            if ($personCount < $minimumPersons) {
                $this->addFlash(
                    'danger',
                    'Le nombre minimum de personnes pour ce menu est de ' . $minimumPersons . '.'
                );

                return $this->render('order/new.html.twig', [
                    'orderForm' => $form->createView(),
                    'menu' => $menu,
                ]);
            }

            $distanceMeters = $deliveryDistanceService->getDistanceInMeters(
                (string) $order->getDeliveryAddress(),
                (string) $order->getDeliveryPostalCode(),
                (string) $order->getDeliveryCity()
            );

            $isBordeaux = $distanceMeters === 0;

            $deliveryPrice = $deliveryFeeCalculator->calculate(
                $isBordeaux,
                $distanceMeters
            );

            $prices = $priceCalculator->calculate(
                (string) $menu->getBasePrice(),
                $personCount,
                $minimumPersons,
                $deliveryPrice
            );

            $order->setUser($user);
            $order->setMenu($menu);
            $order->setStatus('en_attente');
            $order->setCreatedAt(new \DateTimeImmutable());

            $order->setMenuPrice($prices['menuPrice']);
            $order->setDeliveryPrice($prices['deliveryPrice']);
            $order->setDiscountAmount($prices['discountAmount']);
            $order->setTotalPrice($prices['totalPrice']);

            $entityManager->persist($order);

            $history = new OrderStatusHistory();
            $history->setStatus($order->getStatus());
            $history->setChangedAt(new \DateTimeImmutable());
            $history->setCustomerOrder($order);

            $entityManager->persist($history);
            $entityManager->flush();

            $mongoStatsService->incrementOrderStats($order);

            $this->addFlash('success', 'Votre commande a bien été enregistrée.');

            return $this->redirectToRoute('app_user_space');
        }

        return $this->render('order/new.html.twig', [
            'orderForm' => $form->createView(),
            'menu' => $menu,
        ]);
    }
}