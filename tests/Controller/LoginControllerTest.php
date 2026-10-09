<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class LoginControllerTest extends WebTestCase
{
    public function testUserCanLoginSuccessfully(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/connexion');

        self::assertResponseIsSuccessful();

        $client->submitForm('Se connecter', [
            '_username' => 'client@test.local',
            '_password' => 'MotDePasse123!',
        ]);

        self::assertResponseRedirects('/');

        $client->followRedirect();

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('nav', 'Déconnexion');
        self::assertSelectorTextContains('nav', 'Mon compte');
    }

    public function testUserCannotLoginWithInvalidPassword(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/connexion');

        self::assertResponseIsSuccessful();

        $client->submitForm('Se connecter', [
            '_username' => 'client@test.local',
            '_password' => 'MotDePasseFaux!',
        ]);

        self::assertResponseRedirects('/connexion');

        $client->followRedirect();

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('.alert');
    }
}