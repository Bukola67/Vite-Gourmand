<?php

namespace App\Tests\Controller;

use App\Entity\CustomerOrder;
use App\Entity\Menu;
use App\Entity\User;
use App\Repository\CustomerOrderRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

final class CustomerOrderControllerTest extends WebTestCase
{
    public function testClientCanOpenOrderForm(): void
    {
        $client = static::createClient();

        $user = $this->getTestUser();
        $menu = $this->createTestMenu();

        $client->loginUser($user);

        $client->request(
            'GET',
            '/commande/new/'.$menu->getId()
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form');
        self::assertSelectorExists(
            'input[name="customer_order[_token]"]'
        );
    }

    public function testClientCanCreateOrder(): void
    {
        $client = static::createClient();

        $user = $this->getTestUser();
        $userId = $user->getId();

        $menu = $this->createTestMenu(
            minimumPersons: 4,
            stockAvailable: 10
        );

        $menuId = $menu->getId();
        $personCount = 4;

        $orderRepository = static::getContainer()->get(
            CustomerOrderRepository::class
        );

        $numberOfOrdersBefore = $orderRepository->count([]);

        $client->loginUser($user);

        $crawler = $client->request(
            'GET',
            '/commande/new/'.$menuId
        );

        self::assertResponseIsSuccessful();

        $form = $crawler->filter('form')->form(
            $this->validOrderData($personCount)
        );

        $client->submit($form);

        self::assertResponseRedirects(null, 303);

        $location = $client->getResponse()
            ->headers
            ->get('Location');

        self::assertNotNull($location);

        self::assertMatchesRegularExpression(
            '~^/commande/confirmation/\d+$~',
            $location,
            'La création doit rediriger vers la confirmation.'
        );

        // Récupérer les services du conteneur courant après le POST.
        $entityManager = static::getContainer()->get(
            EntityManagerInterface::class
        );

        $entityManager->clear();

        $orderRepository = static::getContainer()->get(
            CustomerOrderRepository::class
        );

        self::assertSame(
            $numberOfOrdersBefore + 1,
            $orderRepository->count([])
        );

        // Le menu est propre à ce test : une seule commande est attendue.
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

        self::assertSame(
            $userId,
            $order->getUser()?->getId()
        );

        self::assertSame('en_attente', $order->getStatus());
        self::assertSame('Bordeaux', $order->getDeliveryCity());
        self::assertSame($personCount, $order->getPersonCount());

        // 25 € × 4 personnes, sans remise et sans livraison à Bordeaux.
        self::assertSame('100.00', $order->getMenuPrice());
        self::assertSame('0.00', $order->getDeliveryPrice());
        self::assertSame('0.00', $order->getDiscountAmount());
        self::assertSame('100.00', $order->getTotalPrice());

        $updatedMenu = $entityManager->find(Menu::class, $menuId);

        self::assertInstanceOf(Menu::class, $updatedMenu);

        self::assertSame(
            6,
            $updatedMenu->getStockAvailable(),
            'Une commande de 4 personnes doit retirer 4 portions.'
        );

        $histories = $order->getOrderStatusHistories();

        self::assertCount(1, $histories);

        $history = $histories->first();

        self::assertInstanceOf(
            \App\Entity\OrderStatusHistory::class,
            $history
        );

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

    public function testClientCannotCreateOrderWithEmptyDeliveryAddress(): void
    {
        $client = static::createClient();

        $user = $this->getTestUser();
        $menu = $this->createTestMenu();
        $menuId = $menu->getId();

        $ordersBefore = static::getContainer()
            ->get(CustomerOrderRepository::class)
            ->count([]);

        $client->loginUser($user);

        $crawler = $client->request(
            'GET',
            '/commande/new/'.$menuId
        );

        self::assertResponseIsSuccessful();

        $data = $this->validOrderData();
        $data['customer_order[deliveryAddress]'] = '';

        $form = $crawler->filter('form')->form($data);

        $client->submit($form);

        self::assertResponseIsSuccessful();

        self::assertSelectorTextContains(
            'form',
            'Ce champ est obligatoire.'
        );

        $this->assertNoOrderCreatedAndStockUnchanged(
            $menuId,
            $ordersBefore,
            expectedStock: 10
        );
    }

    public function testClientCannotCreateOrderWithPersonCountBelowOne(): void
    {
        $client = static::createClient();

        $user = $this->getTestUser();
        $menu = $this->createTestMenu(minimumPersons: 4);
        $menuId = $menu->getId();

        $ordersBefore = static::getContainer()
            ->get(CustomerOrderRepository::class)
            ->count([]);

        $client->loginUser($user);

        $crawler = $client->request(
            'GET',
            '/commande/new/'.$menuId
        );

        self::assertResponseIsSuccessful();

        $form = $crawler->filter('form')->form(
            $this->validOrderData(personCount: 0)
        );

        $client->submit($form);

        self::assertResponseIsSuccessful();

        self::assertSelectorTextContains(
            'form',
            'Ce menu nécessite au minimum 4 personnes.'
        );

        $this->assertNoOrderCreatedAndStockUnchanged(
            $menuId,
            $ordersBefore,
            expectedStock: 10
        );
    }

    private function getTestUser(): User
    {
        $user = static::getContainer()
            ->get(UserRepository::class)
            ->findOneBy([
                'email' => 'client@test.local',
            ]);

        self::assertInstanceOf(
            User::class,
            $user,
            'Le client de test est introuvable. Vérifie les fixtures de test.'
        );

        return $user;
    }

    private function createTestMenu(
        int $minimumPersons = 4,
        int $stockAvailable = 10
    ): Menu {
        $entityManager = static::getContainer()->get(
            EntityManagerInterface::class
        );

        $menu = new Menu();
        $menu->setTitle(
            'Menu test commande '.bin2hex(random_bytes(6))
        );
        $menu->setDescription(
            'Menu dédié aux tests du formulaire de commande.'
        );
        $menu->setTheme('Test');
        $menu->setDiet('Classique');
        $menu->setMinimumPersons($minimumPersons);
        $menu->setBasePrice('25.00');
        $menu->setStockAvailable($stockAvailable);
        $menu->setCreatedAt(new \DateTimeImmutable());

        $entityManager->persist($menu);
        $entityManager->flush();

        self::assertNotNull($menu->getId());

        return $menu;
    }

    /**
     * @return array<string, string>
     */
    private function validOrderData(int $personCount = 4): array
    {
        $deliveryDate = new \DateTimeImmutable(
            '+7 days',
            new \DateTimeZone('Europe/Paris')
        );

        return [
            'customer_order[customerFirstName]' => 'Client',
            'customer_order[customerLastName]' => 'Test',
            'customer_order[customerEmail]' => 'client@test.local',
            'customer_order[customerPhone]' => '0600000000',
            'customer_order[deliveryAddress]' => '10 rue des Tests',
            'customer_order[deliveryPostalCode]' => '33000',
            'customer_order[deliveryCity]' => 'Bordeaux',
            'customer_order[deliveryPlace]' => 'Salle de réception',
            'customer_order[deliveryDate]' => $deliveryDate->format('Y-m-d'),
            'customer_order[deliveryTime]' => '12:00',
            'customer_order[personCount]' => (string) $personCount,
        ];
    }

    private function assertNoOrderCreatedAndStockUnchanged(
        int $menuId,
        int $ordersBefore,
        int $expectedStock
    ): void {
        $entityManager = static::getContainer()->get(
            EntityManagerInterface::class
        );

        $entityManager->clear();

        $orderRepository = static::getContainer()->get(
            CustomerOrderRepository::class
        );

        self::assertSame(
            $ordersBefore,
            $orderRepository->count([]),
            'Un formulaire invalide ne doit créer aucune commande.'
        );

        self::assertSame(
            0,
            $orderRepository->count(['menu' => $menuId])
        );

        $menu = $entityManager->find(Menu::class, $menuId);

        self::assertInstanceOf(Menu::class, $menu);

        self::assertSame(
            $expectedStock,
            $menu->getStockAvailable(),
            'Un formulaire invalide ne doit pas modifier le stock.'
        );
    }
}