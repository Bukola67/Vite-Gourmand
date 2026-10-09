<?php

namespace App\Tests\Service;

use App\Service\GeoplateformeGeocoder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class GeoplateformeGeocoderTest extends TestCase
{
    public function testGeocodeReturnsCoordinates(): void
    {
        $mockResponse = new MockResponse(json_encode([
            'features' => [
                [
                    'geometry' => [
                        'coordinates' => [-0.57918, 44.83779],
                    ],
                ],
            ],
        ]));

        $httpClient = new MockHttpClient($mockResponse);
        $geocoder = new GeoplateformeGeocoder($httpClient);

        $result = $geocoder->geocode('Place de la Bourse, Bordeaux');

        self::assertSame(-0.57918, $result['longitude']);
        self::assertSame(44.83779, $result['latitude']);
    }

    public function testGeocodeThrowsExceptionWhenNoResultIsReturned(): void
    {
        $mockResponse = new MockResponse(json_encode([
            'features' => [],
        ]));

        $httpClient = new MockHttpClient($mockResponse);
        $geocoder = new GeoplateformeGeocoder($httpClient);

        $this->expectException(\RuntimeException::class);

        $geocoder->geocode('Adresse inconnue');

    }
}