<?php

namespace App\Tests\Service;

use App\Entity\CustomerOrder;
use App\Service\MailService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

final class MailServiceTest extends TestCase
{
    public function testConfirmationUsesTheOrderEmailAddress(): void
    {
        $order = new CustomerOrder();
        $order->setCustomerEmail('prestation@example.test');
        $twig = $this->createMock(Environment::class);
        $twig->expects(self::exactly(2))->method('render')->willReturnMap([
            ['emails/order_confirmation.txt.twig', ['order' => $order], 'Confirmation texte'],
            ['emails/order_confirmation.html.twig', ['order' => $order], '<p>Confirmation HTML</p>'],
        ]);
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects(self::once())->method('send')->with(self::callback(
            static fn(Email $email): bool => $email->getTo()[0]->getAddress() === 'prestation@example.test'
                && $email->getTextBody() === 'Confirmation texte'
                && $email->getHtmlBody() === '<p>Confirmation HTML</p>'
        ));
        (new MailService($mailer, $twig))->sendOrderConfirmation($order);
    }
}
