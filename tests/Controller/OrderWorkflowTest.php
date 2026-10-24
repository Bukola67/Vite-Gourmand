<?php

namespace App\Tests\Controller;

use App\Entity\{Menu, User};
use App\Repository\{CustomerOrderRepository, MenuRepository, UserRepository};
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class OrderWorkflowTest extends WebTestCase
{
    private function openForm(): array
    {
        $client = static::createClient();
        $user = static::getContainer()->get(UserRepository::class)->findOneBy(['email' => 'client@test.local']);
        $menu = static::getContainer()->get(MenuRepository::class)->findOneBy([]);
        self::assertInstanceOf(User::class, $user);
        self::assertInstanceOf(Menu::class, $menu);
        $client->loginUser($user);
        $crawler = $client->request('GET', '/commande/new/'.$menu->getId());
        self::assertResponseIsSuccessful();
        $token = $crawler->filter('input[name="customer_order[_token]"]')->attr('value');
        return [$client, $menu, $token, $crawler];
    }

    public function testQuoteAtDiscountThresholdDoesNotCreateAnOrder(): void
    {
        [$client, $menu, $token] = $this->openForm();
        $before = static::getContainer()->get(CustomerOrderRepository::class)->count([]);
        $persons = $menu->getMinimumPersons() + 5;
        $client->request('POST', '/commande/simulation/'.$menu->getId(), ['customer_order' => [
            '_token' => $token,
            'deliveryAddress' => '10 rue des Tests', 'deliveryPostalCode' => '33000',
            'deliveryCity' => 'Bordeaux', 'personCount' => (string) $persons,
        ]]);
        self::assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $parts = explode('.', $menu->getBasePrice());
        $unit = (int) $parts[0] * 100 + (int) str_pad($parts[1] ?? '0', 2, '0');
        $subtotal = $unit * $persons;
        $discount = (int) round($subtotal / 10);
        self::assertSame('0.00', $data['deliveryPrice']);
        self::assertSame(number_format($subtotal / 100, 2, '.', ''), $data['menuPrice']);
        self::assertSame(number_format($discount / 100, 2, '.', ''), $data['discountAmount']);
        self::assertSame(number_format(($subtotal - $discount) / 100, 2, '.', ''), $data['totalPrice']);
        self::assertSame($before, static::getContainer()->get(CustomerOrderRepository::class)->count([]));
    }

    public function testQuoteRejectsBelowMenuMinimum(): void
    {
        [$client, $menu, $token] = $this->openForm();
        $client->request('POST', '/commande/simulation/'.$menu->getId(), ['customer_order' => [
            '_token' => $token,
            'deliveryAddress' => '10 rue des Tests', 'deliveryPostalCode' => '33000',
            'deliveryCity' => 'Bordeaux', 'personCount' => (string) ($menu->getMinimumPersons() - 1),
        ]]);
        self::assertResponseStatusCodeSame(422);
        $data = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertArrayHasKey('personCount', $data['errors']);
    }

    public function testQuoteRejectsInvalidCsrfToken(): void
    {
        [$client, $menu] = $this->openForm();
        $client->request('POST', '/commande/simulation/'.$menu->getId(), ['customer_order' => ['_token' => 'invalid']]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testAllCustomerFieldsAndHelpAreDisplayed(): void
    {
        [$client, $menu, $token, $crawler] = $this->openForm();
        foreach (['customerFirstName', 'customerLastName', 'customerEmail', 'customerPhone', 'deliveryAddress', 'deliveryPostalCode', 'deliveryCity', 'deliveryPlace', 'deliveryDate', 'deliveryTime', 'personCount'] as $name) {
            self::assertSelectorExists('[name="customer_order['.$name.']"]');
            self::assertSelectorExists('#customer_order_'.$name.'_help');
        }
        self::assertSelectorExists('input[name="customer_order[personCount]"][min="'.$menu->getMinimumPersons().'"]');
    }

    public function testFinalSubmissionShowsFieldErrorsWithoutCreatingOrder(): void
    {
        [$client, $menu, $token, $crawler] = $this->openForm();
        $before = static::getContainer()->get(CustomerOrderRepository::class)->count([]);
        $form = $crawler->filter('form[data-order-form]')->form();
        $form['customer_order[customerFirstName]'] = '';
        $form['customer_order[customerLastName]'] = '';
        $form['customer_order[customerEmail]'] = 'incorrect';
        $form['customer_order[customerPhone]'] = 'abc';
        $form['customer_order[deliveryAddress]'] = '';
        $form['customer_order[deliveryPostalCode]'] = '';
        $form['customer_order[deliveryCity]'] = '';
        $form['customer_order[deliveryPlace]'] = '';
        $form['customer_order[deliveryDate]'] = (new \DateTimeImmutable('+7 days'))->format('Y-m-d');
        $form['customer_order[deliveryTime]'] = '12:00';
        $form['customer_order[personCount]'] = (string) ($menu->getMinimumPersons() - 1);
        $client->submit($form);
        self::assertResponseIsSuccessful();
        self::assertSelectorExists('[data-error-summary]');
        self::assertSelectorTextContains('form', 'Ce champ est obligatoire.');
        self::assertSelectorTextContains('form', 'Ce menu nécessite au minimum');
        self::assertSame($before, static::getContainer()->get(CustomerOrderRepository::class)->count([]));
    }
}
