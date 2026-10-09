<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class EmployeeReviewHtmlTest extends WebTestCase
{
    public function testReviewHtmlIsEscapedInEmployeePage(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $userRepository = $container->get(UserRepository::class);
        $employee = $userRepository->findOneBy(['email' => 'employe@test.local']);

        self::assertInstanceOf(User::class, $employee);

        $client->loginUser($employee);
        $client->request('GET', '/employe/avis');

        self::assertResponseIsSuccessful();

        $html = $client->getResponse()->getContent();

        self::assertIsString($html);
        self::assertStringNotContainsString('<script>alert("XSS")</script>', $html);
    }
}