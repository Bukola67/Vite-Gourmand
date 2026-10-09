<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class MenuControllerTest extends WebTestCase
{
    public function testMenusApiReturnsJson(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/menus');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame(
            'Content-Type',
            'application/json'
        );

        $data = json_decode(
            $client->getResponse()->getContent(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $this->assertIsArray($data);
    }

    public function testMenusApiAppliesThemeFilter(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/menus?theme=Noël');

        $this->assertResponseIsSuccessful();

        $data = json_decode(
            $client->getResponse()->getContent(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        foreach ($data as $menu) {
            $this->assertSame('Noël', $menu['theme']);
        }
    }

    public function testMenusApiCanReturnNoResult(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/menus?theme=ThemeInexistant');

        $this->assertResponseIsSuccessful();

        $data = json_decode(
            $client->getResponse()->getContent(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $this->assertSame([], $data);
    }
}