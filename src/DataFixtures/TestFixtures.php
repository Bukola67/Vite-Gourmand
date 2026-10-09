<?php

namespace App\DataFixtures;

use App\Entity\Menu;
use App\Entity\User;
use App\Entity\Review;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class TestFixtures extends Fixture implements FixtureGroupInterface
{
     public static function getGroups(): array
    {
        return ['test'];
    } 

    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $customer = $this->createUser(
            'client@test.local',
            'Client',
            'Test',
            ['ROLE_USER']
        );

        $employee = $this->createUser(
            'employe@test.local',
            'Employe',
            'Test',
            ['ROLE_EMPLOYEE']
        );

        $admin = $this->createUser(
            'admin@test.local',
            'Admin',
            'Test',
            ['ROLE_ADMIN']
        );

        $manager->persist($customer);
        $manager->persist($employee);
        $manager->persist($admin);

        $menu = new Menu();
        $menu->setTitle('Menu Test');
        $menu->setDescription('Menu utilisé pour les tests automatisés.');
        $menu->setTheme('Noël');
        $menu->setDiet('Classique');
        $menu->setMinimumPersons(4);
        $menu->setBasePrice('25.00');
        $menu->setStockAvailable(5);
        $menu->setCreatedAt(new \DateTimeImmutable());

        $manager->persist($menu);

        $reviewPublic = new Review();
        $reviewPublic->setUser($customer);
        $reviewPublic->setRating(5);
        $reviewPublic->setComment('Excellent menu, service rapide et très bon rapport qualité-prix.');
        $reviewPublic->setIsValidated(true);
        $reviewPublic->setCreatedAt(new \DateTimeImmutable('-3 days'));
        $manager->persist($reviewPublic);

        $reviewPending = new Review();
        $reviewPending->setUser($customer);
        $reviewPending->setRating(4);
        $reviewPending->setComment('Livraison correcte, plat encore chaud à l’arrivée.');
        $reviewPending->setIsValidated(false);
        $reviewPending->setCreatedAt(new \DateTimeImmutable('-2 days'));
        $manager->persist($reviewPending);

        $reviewHtml = new Review();
        $reviewHtml->setUser($customer);
        $reviewHtml->setRating(3);
        $reviewHtml->setComment('<b>Très bon</b> menu avec <i>dessert réussi</i>.');
        $reviewHtml->setIsValidated(true);
        $reviewHtml->setCreatedAt(new \DateTimeImmutable('-1 day'));
        $manager->persist($reviewHtml);

        $xssReview = new Review();
        $xssReview->setUser($customer);
        $xssReview->setRating(1);
        $xssReview->setComment('<script>alert("XSS")</script>');
        $xssReview->setIsValidated(false);
        $xssReview->setCreatedAt(new \DateTimeImmutable());
        $manager->persist($xssReview);

        $manager->flush();

        $this->addReference('menu_test', $menu);
        $this->addReference('user_client', $customer);
        $this->addReference('user_employee', $employee);
        $this->addReference('user_admin', $admin);
        $this->addReference('review_xss', $xssReview);
        $this->addReference('review_public', $reviewPublic);
        $this->addReference('review_pending', $reviewPending);
        $this->addReference('review_html', $reviewHtml);
    }

    private function createUser(
        string $email,
        string $firstName,
        string $lastName,
        array $roles
    ): User {
        $user = new User();
        $user->setEmail($email);
        $user->setRoles($roles);
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setPhone('0600000000');
        $user->setAddress('1 rue du Test');
        $user->setPostalCode('33000');
        $user->setCity('Bordeaux');
        $user->setIsActive(true);
        $user->setCreatedAt(new \DateTimeImmutable());

        $user->setPassword(
            $this->passwordHasher->hashPassword($user, 'MotDePasse123!')
        );

        return $user;
    }
}