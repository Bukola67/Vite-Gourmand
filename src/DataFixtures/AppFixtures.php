<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setEmail('user@test.com');
        $user->setRoles(['ROLE_USER']);
        $user->setPassword('$2y$13$.Z6FFTLA.T0221IytqufCOlOxECHqqMKdnuGY2G9z9d4C/812NvCC');
        $user->setFirstName('Test');
        $user->setLastName('User');
        $user->setPhone('0600000000');
        $user->setAddress('1 rue du Test');
        $user->setPostalCode('33000');
        $user->setCity('Bordeaux');
        $user->setIsActive(true);
        $user->setCreateAt(new \DateTimeImmutable());

        $manager->persist($user);
        $manager->flush();
    }
}