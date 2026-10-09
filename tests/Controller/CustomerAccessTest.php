<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CustomerAccessTest extends WebTestCase
{
    public function testAccountPageRedirectsWhenUserIsNotAuthenticated(): void
    {
        $client = static::createClient();

        $client->request('GET', '/compte');

        self::assertResponseRedirects('/connexion');
    }

    public function testCustomerCanAccessAccountPage(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $userRepository = $container->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'client@test.local']);

        self::assertInstanceOf(User::class, $user);

        $client->loginUser($user);
        $client->request('GET', '/compte');

        self::assertResponseIsSuccessful();
    }
}