<?php

namespace App\Controller;

use App\Entity\{CustomerOrder, Menu, OrderStatusHistory, User};
use App\Form\CustomerOrderType;
use App\Service\{MailService, MongoStatsService, OrderQuoteCalculator};
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\{JsonResponse, Request, Response};
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/commande', name: 'app_order_')]
final class OrderController extends AbstractController
{
    #[Route('/new/{id}', name: 'new', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function new(
        Menu $menu,
        Request $request,
        EntityManagerInterface $entityManager,
        MongoStatsService $mongoStatsService,
        OrderQuoteCalculator $quoteCalculator,
        MailService $mailService,
        LoggerInterface $logger,
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $order = new CustomerOrder();
        $order->setCustomerFirstName((string) $user->getFirstName());
        $order->setCustomerLastName((string) $user->getLastName());
        $order->setCustomerEmail((string) $user->getEmail());
        $order->setCustomerPhone((string) $user->getPhone());
        $order->setDeliveryAddress((string) $user->getAddress());
        $order->setDeliveryPostalCode((string) $user->getPostalCode());
        $order->setDeliveryCity((string) $user->getCity());
        $order->setPersonCount((int) $menu->getMinimumPersons());

        $form = $this->createForm(CustomerOrderType::class, $order, [
            'minimum_persons' => (int) $menu->getMinimumPersons(),
            'action' => $this->generateUrl('app_order_new', ['id' => $menu->getId()]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Le navigateur ne fournit jamais les montants de confiance.
                $prices = $quoteCalculator->calculate($menu, $order);
            } catch (\Exception $exception) {
                $logger->error('Calcul de commande impossible.', ['exception' => $exception, 'menu_id' => $menu->getId()]);
                $form->addError(new FormError('Le prix de livraison ne peut pas être calculé pour le moment. Vérifiez l’adresse ou réessayez plus tard. Aucune commande n’a été créée.'));
            }

            if (isset($prices)) {
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

                // Une panne de statistiques ne doit pas transformer une commande créée en échec apparent.
                try {
                    $mongoStatsService->incrementOrderStats($order);
                } catch (\Exception $exception) {
                    $logger->error('Statistiques non mises à jour.', ['exception' => $exception, 'order_id' => $order->getId()]);
                }

                try {
                    $mailService->sendOrderConfirmation($order);
                    $this->addFlash('success', 'Votre commande a bien été enregistrée. L’envoi de votre confirmation par e-mail a été pris en charge.');
                } catch (TransportExceptionInterface $exception) {
                    $logger->error('Envoi de confirmation impossible.', ['exception' => $exception, 'order_id' => $order->getId()]);
                    $this->addFlash('warning', 'Votre commande est bien enregistrée, mais la confirmation par e-mail n’a pas pu être envoyée. Ne recommandez pas : retrouvez votre commande dans votre espace client.');
                }

                return $this->redirectToRoute('app_user_space');
            }
        }

        // Le GET partiel sert uniquement à charger la modale. Les erreurs POST restent sur la page complète.
        $template = $request->isMethod('GET') && $request->query->get('fragment') === '1'
            ? 'order/_form.html.twig'
            : 'order/new.html.twig';
        $response = $this->render($template, ['orderForm' => $form->createView(), 'menu' => $menu]);
        $response->setPrivate();
        $response->headers->set('Cache-Control', 'private, no-store');
        return $response;
    }

    #[Route('/simulation/{id}', name: 'quote', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function quote(
        Menu $menu,
        Request $request,
        ValidatorInterface $validator,
        OrderQuoteCalculator $quoteCalculator,
        LoggerInterface $logger,
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $data = $request->request->all('customer_order');
        $token = $data['_token'] ?? null;
        if (!is_string($token) || !$this->isCsrfTokenValid('customer_order', $token)) {
            return $this->json(['message' => 'Votre session a expiré. Rechargez la page.'], 403);
        }

        $errors = [];
        $values = [];
        foreach (['deliveryAddress' => 255, 'deliveryPostalCode' => 20, 'deliveryCity' => 100] as $field => $max) {
            $value = $data[$field] ?? '';
            if (!is_string($value)) {
                $errors[$field] = 'Valeur invalide.';
                continue;
            }
            $values[$field] = trim($value);
            $violations = $validator->validate($values[$field], [
                new Assert\NotBlank(message: 'Ce champ est nécessaire pour calculer la livraison.'),
                new Assert\Length(max: $max, maxMessage: 'Saisissez au maximum {{ limit }} caractères.'),
            ]);
            if (count($violations) > 0) {
                $errors[$field] = $violations[0]->getMessage();
            }
        }
        $rawCount = $data['personCount'] ?? '';
        $personCount = is_string($rawCount) ? filter_var($rawCount, FILTER_VALIDATE_INT) : false;
        if ($personCount === false || $personCount < $menu->getMinimumPersons()) {
            $errors['personCount'] = sprintf('Saisissez un entier supérieur ou égal à %d.', $menu->getMinimumPersons());
        }
        if ($errors !== []) {
            return $this->json(['message' => 'Complétez les informations nécessaires au prix.', 'errors' => $errors], 422);
        }

        $order = new CustomerOrder();
        $order->setDeliveryAddress($values['deliveryAddress']);
        $order->setDeliveryPostalCode($values['deliveryPostalCode']);
        $order->setDeliveryCity($values['deliveryCity']);
        $order->setPersonCount($personCount);
        try {
            $prices = $quoteCalculator->calculate($menu, $order);
        } catch (\Exception $exception) {
            $logger->error('Simulation de commande impossible.', ['exception' => $exception, 'menu_id' => $menu->getId()]);
            return $this->json(['message' => 'Livraison impossible à calculer. Vérifiez l’adresse ou réessayez plus tard.'], 503);
        }

        $response = $this->json($prices + ['unitPrice' => $menu->getBasePrice(), 'personCount' => $personCount]);
        $response->headers->set('Cache-Control', 'private, no-store');
        return $response;
    }
}
