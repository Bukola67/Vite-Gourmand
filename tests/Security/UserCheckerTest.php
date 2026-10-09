<?php

namespace App\Tests\Security;

use App\Entity\User;
use App\Security\UserChecker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

class UserCheckerTest extends TestCase
{
    public function testActiveUserIsAccepted(): void
    {
        $checker = new UserChecker();
        $user = new User();
        $user->setIsActive(true);

        $checker->checkPreAuth($user);

        $this->addToAssertionCount(1);
    }

    public function testInactiveUserIsRejected(): void
    {
        $this->expectException(
            CustomUserMessageAuthenticationException::class
        );
        $this->expectExceptionMessage(
            'Votre compte est désactivé. Veuillez contacter un administrateur.'
        );

        $checker = new UserChecker();
        $user = new User();
        $user->setIsActive(false);

        $checker->checkPreAuth($user);
    }
}