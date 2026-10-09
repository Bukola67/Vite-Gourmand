<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class EmployeeAccessTest extends WebTestCase
{
    public function testEmployeeOrdersPageRedirectsWhenUserIsNotAuthenticated(): void
    {
        $client = static::createClient();

        $client->request('GET', '/employe/commandes');

        self::assertResponseRedirects('/connexion');
    }

    public function testEmployeeCanAccessOrdersPage(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $userRepository = $container->get(UserRepository::class);
        $employee = $userRepository->findOneBy(['email' => 'employe@test.local']);

        self::assertInstanceOf(User::class, $employee);

        $client->loginUser($employee);
        $client->request('GET', '/employe/commandes');

        self::assertResponseIsSuccessful();
    }

    public function testRegularUserCannotAccessOrdersPage(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $userRepository = $container->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'client@test.local']);

        self::assertInstanceOf(User::class, $user);

        $client->loginUser($user);
        $client->request('GET', '/employe/commandes');

        self::assertResponseStatusCodeSame(403);
    }
}