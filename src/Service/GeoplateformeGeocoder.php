<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

final class GeoplateformeGeocoder implements GeocoderInterface
{
    private const BASE_URI = 'https://data.geopf.fr/geocodage/search';

    public function __construct(
        private HttpClientInterface $httpClient,
    ) {
    }

    public function geocode(string $address): array
    {
        $response = $this->httpClient->request('GET', self::BASE_URI, [
            'query' => [
                'q' => $address,
                'limit' => 1,
            ],
        ]);

        $data = $response->toArray();

        if (
            !isset($data['features'][0]['geometry']['coordinates'][0]) ||
            !isset($data['features'][0]['geometry']['coordinates'][1])
        ) {
            throw new \RuntimeException('Adresse introuvable.');
        }

        return [
            'longitude' => (float) $data['features'][0]['geometry']['coordinates'][0],
            'latitude' => (float) $data['features'][0]['geometry']['coordinates'][1],
        ];
    }
}