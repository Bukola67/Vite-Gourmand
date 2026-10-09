<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class AdminAccessTest extends WebTestCase
{
    public function testAdminPageRedirectsWhenUserIsNotAuthenticated(): void
    {
        $client = static::createClient();

        $client->request('GET', '/admin/employes/new');

        self::assertResponseRedirects('/connexion');
    }

    public function testAdminCanAccessEmployeeCreationPage(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $userRepository = $container->get(UserRepository::class);
        $admin = $userRepository->findOneBy(['email' => 'admin@test.local']);

        self::assertInstanceOf(User::class, $admin);

        $client->loginUser($admin);
        $client->request('GET', '/admin/employes/new');

        self::assertResponseIsSuccessful();
    }

    public function testEmployeeCannotAccessAdminPage(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $userRepository = $container->get(UserRepository::class);
        $employee = $userRepository->findOneBy(['email' => 'employe@test.local']);

        self::assertInstanceOf(User::class, $employee);

        $client->loginUser($employee);
        $client->request('GET', '/admin/employes/new');

        self::assertResponseStatusCodeSame(403);
    }
}