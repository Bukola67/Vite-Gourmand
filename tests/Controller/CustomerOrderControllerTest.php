<?php

namespace App\Tests\Controller;

use App\Entity\Menu;
use App\Entity\User;
use App\Repository\MenuRepository;
use App\Repository\UserRepository;
use App\Repository\CustomerOrderRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CustomerOrderControllerTest extends WebTestCase
{
    public function testClientCanOpenOrderForm(): void
    {
        $client = static::createClient();

        $userRepository = static::getContainer()->get(UserRepository::class);
        $menuRepository = static::getContainer()->get(MenuRepository::class);

        $user = $userRepository->findOneBy([
            'email' => 'client@test.local',
        ]);

        $menu = $menuRepository->findOneBy([]);

        self::assertInstanceOf(
            User::class,
            $user,
            'Le client de test est introuvable.'
        );

        self::assertInstanceOf(
            Menu::class,
            $menu,
            'Aucun menu de test n’est disponible.'
        );

        $client->loginUser($user);

        $client->request(
            'GET',
            sprintf('/commande/new/%d', $menu->getId())
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form');
    }

    public function testClientCanCreateOrder(): void
    {
        $client = static::createClient();

        $container = static::getContainer();

        $userRepository = $container->get(UserRepository::class);
        $menuRepository = $container->get(MenuRepository::class);
        $orderRepository = $container->get(CustomerOrderRepository::class);

        $user = $userRepository->findOneBy([
            'email' => 'client@test.local',
        ]);

        $menu = $menuRepository->findOneBy([]);

        self::assertInstanceOf(
            User::class,
            $user,
            'Le client de test est introuvable.'
        );

        self::assertInstanceOf(
            Menu::class,
            $menu,
            'Aucun menu de test n’est disponible.'
        );

        $numberOfOrdersBefore = $orderRepository->count([]);

        $client->loginUser($user);

        $crawler = $client->request(
            'GET',
            sprintf('/commande/new/%d', $menu->getId())
        );

        self::assertResponseIsSuccessful();

        $form = $crawler->filter('form')->form();

        $form['customer_order[deliveryAddress]'] = '10 rue des Tests';
        $form['customer_order[deliveryPostalCode]'] = '33000';
        $form['customer_order[deliveryCity]'] = 'Bordeaux';
        $form['customer_order[deliveryPlace]'] = 'Salle de réception';
        $form['customer_order[deliveryDate]'] = (new \DateTimeImmutable('+7 days'))->format('Y-m-d');
        $form['customer_order[deliveryTime]'] = '12:00';
        $form['customer_order[personCount]'] = (string) $menu->getMinimumPersons();

        $client->submit($form);

        self::assertResponseRedirects('/compte');

        self::assertSame(
            $numberOfOrdersBefore + 1,
            $orderRepository->count([])
        );

        $order = $orderRepository->findOneBy(
            ['user' => $user],
            ['id' => 'DESC']
        );

        self::assertNotNull($order);

        self::assertSame('en_attente', $order->getStatus());
        self::assertSame('Bordeaux', $order->getDeliveryCity());

        self::assertSame(
            number_format(
                (float) $menu->getBasePrice() * $menu->getMinimumPersons(),
                2,
                '.',
                ''
            ),
            $order->getMenuPrice()
        );

        self::assertSame('0.00', $order->getDeliveryPrice());

        self::assertSame(
            number_format(
                (float) $menu->getBasePrice() * $menu->getMinimumPersons(),
                2,
                '.',
                ''
            ),
            $order->getTotalPrice()
        );
    }

    public function testClientCannotCreateOrderWithEmptyDeliveryAddress(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $userRepository = $container->get(UserRepository::class);
        $menuRepository = $container->get(MenuRepository::class);
        $orderRepository = $container->get(CustomerOrderRepository::class);

        $user = $userRepository->findOneBy(['email' => 'client@test.local']);
        $menu = $menuRepository->findOneBy([]);

        self::assertInstanceOf(User::class, $user);
        self::assertInstanceOf(Menu::class, $menu);

        $ordersBefore = $orderRepository->count([]);

        $client->loginUser($user);

        $crawler = $client->request('GET', sprintf('/commande/new/%d', $menu->getId()));

        self::assertResponseIsSuccessful();

        $form = $crawler->filter('form')->form([
            'customer_order[deliveryAddress]' => '',
            'customer_order[deliveryPostalCode]' => '33000',
            'customer_order[deliveryCity]' => 'Bordeaux',
            'customer_order[deliveryPlace]' => 'Domicile',
            'customer_order[deliveryDate]' => (new \DateTimeImmutable('+7 days'))->format('Y-m-d'),
            'customer_order[deliveryTime]' => '12:00',
            'customer_order[personCount]' => (string) $menu->getMinimumPersons(),
        ]);

        $client->submit($form);

        self::assertResponseIsSuccessful();self::assertResponseIsSuccessful();
        self::assertSame($ordersBefore, $orderRepository->count([]));
        self::assertSelectorTextContains('form', 'L’adresse de livraison est obligatoire.');
    }

    public function testClientCannotCreateOrderWithPersonCountBelowOne(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $userRepository = $container->get(UserRepository::class);
        $menuRepository = $container->get(MenuRepository::class);
        $orderRepository = $container->get(CustomerOrderRepository::class);

        $user = $userRepository->findOneBy(['email' => 'client@test.local']);
        $menu = $menuRepository->findOneBy([]);

        self::assertInstanceOf(User::class, $user);
        self::assertInstanceOf(Menu::class, $menu);

        $ordersBefore = $orderRepository->count([]);

        $client->loginUser($user);

        $crawler = $client->request('GET', sprintf('/commande/new/%d', $menu->getId()));

        self::assertResponseIsSuccessful();

        $form = $crawler->filter('form')->form([
            'customer_order[deliveryAddress]' => '10 rue des Tests',
            'customer_order[deliveryPostalCode]' => '33000',
            'customer_order[deliveryCity]' => 'Bordeaux',
            'customer_order[deliveryPlace]' => 'Domicile',
            'customer_order[deliveryDate]' => (new \DateTimeImmutable('+7 days'))->format('Y-m-d'),
            'customer_order[deliveryTime]' => '12:00',
            'customer_order[personCount]' => '0',
        ]);

        $client->submit($form);

        self::assertResponseIsSuccessful();self::assertResponseIsSuccessful();
        self::assertSame($ordersBefore, $orderRepository->count([]));
        self::assertSelectorTextContains('form', 'Le nombre de personnes doit être supérieur ou égal à 1.');
    }

}