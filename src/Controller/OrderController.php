<?php

namespace App\Controller;

use App\Entity\{CustomerOrder, Menu, OrderStatusHistory, User};
use App\Form\CustomerOrderType;
use App\Service\{MailService, OrderQuoteCalculator};
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
    #[Route(
        '/new/{id}',
        name: 'new',
        requirements: ['id' => '\d+'],
        methods: ['GET', 'POST']
    )]
    public function new(
        Menu $menu,
        Request $request,
        EntityManagerInterface $entityManager,
        OrderQuoteCalculator $quoteCalculator,
        MailService $mailService,
        LoggerInterface $logger,
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        // Les coordonnées du compte sont copiées dans la commande.
        // Leur modification ici ne modifie pas le profil utilisateur.
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
            'action' => $this->generateUrl(
                'app_order_new',
                ['id' => $menu->getId()]
            ),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $prices = null;

            try {
                // Les montants sont toujours recalculés côté serveur.
                $prices = $quoteCalculator->calculate($menu, $order);
            } catch (\Exception $exception) {
                $logger->error(
                    'Calcul de commande impossible.',
                    [
                        'exception' => $exception,
                        'menu_id' => $menu->getId(),
                    ]
                );

                $form->addError(new FormError(
                    'Le prix de livraison ne peut pas être calculé '
                    . 'pour le moment. Vérifiez l’adresse ou réessayez '
                    . 'plus tard. Aucune commande n’a été créée.'
                ));
            }

            if ($prices !== null) {
                $now = new \DateTimeImmutable();

                $order->setUser($user);
                $order->setMenu($menu);
                $order->setStatus('en_attente');
                $order->setCreatedAt($now);
                $order->setMenuPrice($prices['menuPrice']);
                $order->setDeliveryPrice($prices['deliveryPrice']);
                $order->setDiscountAmount($prices['discountAmount']);
                $order->setTotalPrice($prices['totalPrice']);

                $history = new OrderStatusHistory();
                $history->setStatus('en_attente');
                $history->setChangedAt($now);

                $order->addOrderStatusHistory($history);

                try {
                    $entityManager->persist($order);
                    $entityManager->persist($history);
                    $entityManager->flush();
                } catch (\Exception $exception) {
                    $logger->error(
                        'Échec de l’enregistrement de la commande.',
                        [
                            'exception' => $exception,
                            'user_id' => $user->getId(),
                            'menu_id' => $menu->getId(),
                        ]
                    );

                    $this->addFlash(
                        'danger',
                        'L’enregistrement de votre commande n’a pas pu '
                        . 'être confirmé. Consultez votre espace client '
                        . 'avant toute nouvelle tentative afin d’éviter '
                        . 'un doublon.'
                    );

                    // Pas de page de confirmation si l’enregistrement
                    // n’a pas pu être confirmé.
                    return $this->redirectToRoute(
                        'app_user_space',
                        [],
                        Response::HTTP_SEE_OTHER
                    );
                }

                // Les statistiques MongoDB sont mises à jour au passage
                // à « terminee », pas à la création de la commande.

                $emailStatus = 'handled';

                try {
                    $mailService->sendOrderConfirmation($order);
                } catch (TransportExceptionInterface $exception) {
                    $emailStatus = 'failed';

                    $logger->error(
                        'Envoi de confirmation impossible.',
                        [
                            'exception' => $exception,
                            'order_id' => $order->getId(),
                        ]
                    );
                }

                // État temporaire associé à cette commande dans la session.
                // « handled » ne garantit pas la réception dans la boîte mail.
                $request->getSession()->set(
                    'order_confirmation_email_'.$order->getId(),
                    $emailStatus
                );

                // Après création réussie, afficher la page de remerciement.
                return $this->redirectToRoute(
                    'app_order_confirmation',
                    ['id' => $order->getId()],
                    Response::HTTP_SEE_OTHER
                );
            }
        }

        // Fragment pour la modale ou page complète.
        $template = (
            $request->isMethod('GET')
            && $request->query->get('fragment') === '1'
        )
            ? 'order/_form.html.twig'
            : 'order/new.html.twig';

        $response = $this->render($template, [
            'orderForm' => $form->createView(),
            'menu' => $menu,
        ]);

        $response->setPrivate();
        $response->headers->set(
            'Cache-Control',
            'private, no-store'
        );

        return $response;
    }

    #[Route(
        '/confirmation/{id}',
        name: 'confirmation',
        requirements: ['id' => '\d+'],
        methods: ['GET']
    )]
    public function confirmation(
        CustomerOrder $order,
        Request $request,
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        // Seul le propriétaire peut consulter cette confirmation.
        if (
            !$user instanceof User
            || $order->getUser()?->getId() !== $user->getId()
        ) {
            throw $this->createAccessDeniedException(
                'Accès refusé à cette commande.'
            );
        }

        $emailStatus = $request->getSession()->get(
            'order_confirmation_email_'.$order->getId(),
            'unknown'
        );

        $response = $this->render(
            'order/confirmation.html.twig',
            [
                'order' => $order,
                'emailStatus' => $emailStatus,
            ]
        );

        $response->setPrivate();
        $response->headers->set(
            'Cache-Control',
            'private, no-store'
        );

        return $response;
    }

    #[Route(
        '/simulation/{id}',
        name: 'quote',
        requirements: ['id' => '\d+'],
        methods: ['POST']
    )]
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

        if (
            !is_string($token)
            || !$this->isCsrfTokenValid('customer_order', $token)
        ) {
            return $this->json(
                [
                    'message' => 'Votre session a expiré. Rechargez la page.',
                ],
                Response::HTTP_FORBIDDEN
            );
        }

        $errors = [];
        $values = [];

        foreach (
            [
                'deliveryAddress' => 255,
                'deliveryPostalCode' => 20,
                'deliveryCity' => 100,
            ] as $field => $max
        ) {
            $value = $data[$field] ?? '';

            if (!is_string($value)) {
                $errors[$field] = 'Valeur invalide.';
                continue;
            }

            $values[$field] = trim($value);

            $violations = $validator->validate(
                $values[$field],
                [
                    new Assert\NotBlank(
                        message: 'Ce champ est nécessaire pour calculer '
                            . 'la livraison.'
                    ),
                    new Assert\Length(
                        max: $max,
                        maxMessage: 'Saisissez au maximum {{ limit }} caractères.'
                    ),
                ]
            );

            if (count($violations) > 0) {
                $errors[$field] = $violations[0]->getMessage();
            }
        }

        $rawCount = $data['personCount'] ?? '';

        $personCount = is_string($rawCount)
            ? filter_var($rawCount, FILTER_VALIDATE_INT)
            : false;

        if (
            $personCount === false
            || $personCount < $menu->getMinimumPersons()
        ) {
            $errors['personCount'] = sprintf(
                'Saisissez un entier supérieur ou égal à %d.',
                $menu->getMinimumPersons()
            );
        }

        if ($errors !== []) {
            return $this->json(
                [
                    'message' => 'Complétez les informations nécessaires au prix.',
                    'errors' => $errors,
                ],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $order = new CustomerOrder();
        $order->setDeliveryAddress($values['deliveryAddress']);
        $order->setDeliveryPostalCode($values['deliveryPostalCode']);
        $order->setDeliveryCity($values['deliveryCity']);
        $order->setPersonCount($personCount);

        try {
            $prices = $quoteCalculator->calculate($menu, $order);
        } catch (\Exception $exception) {
            $logger->error(
                'Simulation de commande impossible.',
                [
                    'exception' => $exception,
                    'menu_id' => $menu->getId(),
                ]
            );

            return $this->json(
                [
                    'message' => 'Livraison impossible à calculer. '
                        . 'Vérifiez l’adresse ou réessayez plus tard.',
                ],
                Response::HTTP_SERVICE_UNAVAILABLE
            );
        }

        $response = $this->json(
            $prices + [
                'unitPrice' => $menu->getBasePrice(),
                'personCount' => $personCount,
            ]
        );

        $response->headers->set(
            'Cache-Control',
            'private, no-store'
        );

        return $response;
    }
}