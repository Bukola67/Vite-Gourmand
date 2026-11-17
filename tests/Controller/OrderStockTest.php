<?php

namespace App\Tests\Controller;

use App\Entity\CustomerOrder;
use App\Entity\Menu;
use App\Entity\OrderStatusHistory;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class OrderStockTest extends WebTestCase
{
    public function testSuccessfulOrderCreatesHistoryAndDecreasesStock(): void
    {
        // 10 portions disponibles, commande de 4 personnes.
        $this->assertSuccessfulOrder(
            initialStock: 10,
            personCount: 4,
            expectedStock: 6
        );
    }

    public function testInsufficientStockDoesNotCreateOrderOrHistory(): void
    {
        $client = static::createClient();

        // Le minimum du menu est de 4 personnes,
        // mais seulement 3 portions sont disponibles.
        $menuId = $this->createMenu(initialStock: 3);

        $entityManager = $this->freshEntityManager();

        $ordersBefore = $entityManager
            ->getRepository(CustomerOrder::class)
            ->count([]);

        $historiesBefore = $entityManager
            ->getRepository(OrderStatusHistory::class)
            ->count([]);

        $this->submitOrder($client, $menuId, personCount: 4);

        self::assertResponseRedirects(
            '/commande/new/'.$menuId,
            303
        );

        // La transaction en échec peut avoir fermé l'EntityManager.
        $entityManager = $this->freshEntityManager();

        self::assertSame(
            $ordersBefore,
            $entityManager
                ->getRepository(CustomerOrder::class)
                ->count([]),
            'Aucune commande ne doit être créée si le stock est insuffisant.'
        );

        self::assertSame(
            $historiesBefore,
            $entityManager
                ->getRepository(OrderStatusHistory::class)
                ->count([]),
            'Aucun historique ne doit être créé si le stock est insuffisant.'
        );

        $menu = $entityManager->find(Menu::class, $menuId);

        self::assertInstanceOf(Menu::class, $menu);

        self::assertSame(
            3,
            $menu->getStockAvailable(),
            'Le stock doit rester inchangé après le refus.'
        );

        self::assertSame(
            0,
            $entityManager
                ->getRepository(CustomerOrder::class)
                ->count(['menu' => $menuId])
        );
    }

    public function testExactlySufficientStockAcceptsOrderAndLeavesZeroStock(): void
    {
        // 4 portions disponibles, commande de 4 personnes.
        $this->assertSuccessfulOrder(
            initialStock: 4,
            personCount: 4,
            expectedStock: 0
        );
    }

    private function assertSuccessfulOrder(
        int $initialStock,
        int $personCount,
        int $expectedStock
    ): void {
        $client = static::createClient();

        $menuId = $this->createMenu($initialStock);

        $entityManager = $this->freshEntityManager();

        $ordersBefore = $entityManager
            ->getRepository(CustomerOrder::class)
            ->count([]);

        $historiesBefore = $entityManager
            ->getRepository(OrderStatusHistory::class)
            ->count([]);

        $this->submitOrder($client, $menuId, $personCount);

        self::assertResponseRedirects(null, 303);

        // Relire les données persistées, pas les objets d'avant le POST.
        $entityManager = $this->freshEntityManager();

        $orderRepository = $entityManager
            ->getRepository(CustomerOrder::class);

        self::assertSame(
            $ordersBefore + 1,
            $orderRepository->count([]),
            'Une seule commande doit être créée.'
        );

        self::assertSame(
            1,
            $orderRepository->count(['menu' => $menuId])
        );

        $order = $orderRepository->findOneBy([
            'menu' => $menuId,
        ]);

        self::assertInstanceOf(CustomerOrder::class, $order);

        self::assertResponseRedirects(
            '/commande/confirmation/'.$order->getId(),
            303
        );

        self::assertSame('en_attente', $order->getStatus());
        self::assertSame($personCount, $order->getPersonCount());

        $menu = $entityManager->find(Menu::class, $menuId);

        self::assertInstanceOf(Menu::class, $menu);

        self::assertSame(
            $expectedStock,
            $menu->getStockAvailable(),
            'Le stock doit diminuer du nombre de personnes commandées.'
        );

        self::assertSame(
            $historiesBefore + 1,
            $entityManager
                ->getRepository(OrderStatusHistory::class)
                ->count([]),
            'Un seul historique initial doit être créé.'
        );

        $histories = $order->getOrderStatusHistories();

        self::assertCount(1, $histories);

        $history = $histories->first();

        self::assertInstanceOf(OrderStatusHistory::class, $history);
        self::assertNotNull($history->getId());
        self::assertSame('en_attente', $history->getStatus());

        self::assertInstanceOf(
            \DateTimeImmutable::class,
            $history->getChangedAt()
        );

        self::assertSame(
            $order->getId(),
            $history->getCustomerOrder()?->getId()
        );
    }

    private function createMenu(int $initialStock): int
    {
        $entityManager = static::getContainer()->get(
            EntityManagerInterface::class
        );

        $menu = new Menu();
        $menu->setTitle('Menu test stock '.bin2hex(random_bytes(6)));
        $menu->setDescription('Menu dédié aux tests de réservation du stock.');
        $menu->setTheme('Test');
        $menu->setDiet('Classique');
        $menu->setMinimumPersons(4);
        $menu->setBasePrice('25.00');
        $menu->setStockAvailable($initialStock);
        $menu->setCreatedAt(new \DateTimeImmutable());

        $entityManager->persist($menu);
        $entityManager->flush();

        $menuId = $menu->getId();

        self::assertNotNull($menuId);

        return $menuId;
    }

    private function submitOrder(
        KernelBrowser $client,
        int $menuId,
        int $personCount
    ): void {
        $entityManager = static::getContainer()->get(
            EntityManagerInterface::class
        );

        $user = $entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'client@test.local']);

        self::assertInstanceOf(
            User::class,
            $user,
            'Le client de test est introuvable. Charge les fixtures de test.'
        );

        $client->loginUser($user);

        $crawler = $client->request(
            'GET',
            '/commande/new/'.$menuId
        );

        self::assertResponseIsSuccessful();

        // Le formulaire récupéré contient le véritable jeton CSRF.
        $form = $crawler->filter('form')->form([
            'customer_order[customerFirstName]' => 'Client',
            'customer_order[customerLastName]' => 'Test',
            'customer_order[customerEmail]' => 'client@test.local',
            'customer_order[customerPhone]' => '0600000000',
            'customer_order[deliveryAddress]' => '10 rue des Tests',
            'customer_order[deliveryPostalCode]' => '33000',
            'customer_order[deliveryCity]' => 'Bordeaux',
            'customer_order[deliveryPlace]' => 'Salle de réception',
            'customer_order[deliveryDate]' => (
                new \DateTimeImmutable(
                    '+7 days',
                    new \DateTimeZone('Europe/Paris')
                )
            )->format('Y-m-d'),
            'customer_order[deliveryTime]' => '12:00',
            'customer_order[personCount]' => (string) $personCount,
        ]);

        $client->submit($form);
    }

    private function freshEntityManager(): EntityManagerInterface
    {
        $registry = static::getContainer()->get(ManagerRegistry::class);

        $entityManager = $registry->getManager();

        self::assertInstanceOf(
            EntityManagerInterface::class,
            $entityManager
        );

        if (!$entityManager->isOpen()) {
            $entityManager = $registry->resetManager();

            self::assertInstanceOf(
                EntityManagerInterface::class,
                $entityManager
            );
        }

        $entityManager->clear();

        return $entityManager;
    }
}