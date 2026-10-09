<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class SecurityAccessTest extends WebTestCase
{
    public function testAnonymousUserCannotAccessAccount(): void
    {
        $client = static::createClient();

        $client->request('GET', '/compte');

        self::assertResponseRedirects('/connexion');
    }

    public function testClientCanAccessAccount(): void
    {
        $client = static::createClient();
        $this->loginAs($client, 'client@test.local');

        $client->request('GET', '/compte');

        self::assertResponseIsSuccessful();
    }

    public function testClientCannotAccessEmployeeSpace(): void
    {
        $client = static::createClient();
        $this->loginAs($client, 'client@test.local');

        $client->request('GET', '/employe/commandes');

        self::assertResponseStatusCodeSame(403);
    }

    public function testClientCannotAccessAdminSpace(): void
    {
        $client = static::createClient();
        $this->loginAs($client, 'client@test.local');

        $client->request('GET', '/admin');

        self::assertResponseStatusCodeSame(403);
    }

    public function testEmployeeCanAccessAccount(): void
    {
        $client = static::createClient();
        $this->loginAs($client, 'employe@test.local');

        $client->request('GET', '/compte');

        self::assertResponseIsSuccessful();
    }

    public function testEmployeeCanAccessEmployeeSpace(): void
    {
        $client = static::createClient();
        $this->loginAs($client, 'employe@test.local');

        $client->request('GET', '/employe/commandes');

        self::assertResponseIsSuccessful();
    }

    public function testEmployeeCannotAccessAdminSpace(): void
    {
        $client = static::createClient();
        $this->loginAs($client, 'employe@test.local');

        $client->request('GET', '/admin');

        self::assertResponseStatusCodeSame(403);
    }

    public function testAdminCanAccessAccount(): void
    {
        $client = static::createClient();
        $this->loginAs($client, 'admin@test.local');

        $client->request('GET', '/compte');

        self::assertResponseIsSuccessful();
    }

    public function testAdminCanAccessEmployeeSpace(): void
    {
        $client = static::createClient();
        $this->loginAs($client, 'admin@test.local');

        $client->request('GET', '/employe/commandes');

        self::assertResponseIsSuccessful();
    }

    public function testAdminCanAccessAdminSpace(): void
    {
        $client = static::createClient();
        $this->loginAs($client, 'admin@test.local');

        $client->request('GET', '/admin');

        self::assertResponseIsSuccessful();
    }

    private function loginAs(KernelBrowser $client, string $email): User
    {
        $repository = static::getContainer()->get(UserRepository::class);
        $user = $repository->findOneBy(['email' => $email]);

        self::assertInstanceOf(
            User::class,
            $user,
            sprintf('L’utilisateur de test %s est introuvable.', $email)
        );

        $client->loginUser($user);

        return $user;
    }
}