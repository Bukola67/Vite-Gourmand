<?php

namespace App\Service;

interface GeocoderInterface
{
    public function geocode(string $fullAddress): array;
}