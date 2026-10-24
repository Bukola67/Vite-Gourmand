<?php

namespace App\Service;

use App\Entity\CustomerOrder;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class MailService
{
    private string $fromEmail = 'vite.et.gourmand.studi@gmail.com';

    public function __construct(private MailerInterface $mailer, private Environment $twig)
    {
    }

    public function sendOrderConfirmation(CustomerOrder $order): void
    {
        $context = ['order' => $order];
        $email = (new Email())
            ->from($this->fromEmail)
            ->to((string) $order->getCustomerEmail())
            ->subject(sprintf('Confirmation de votre commande n°%d — Vite & Gourmand', $order->getId()))
            ->text($this->twig->render('emails/order_confirmation.txt.twig', $context))
            ->html($this->twig->render('emails/order_confirmation.html.twig', $context));

        $this->mailer->send($email);
    }

    public function sendEquipmentReturnReminder(CustomerOrder $order): void
    {
        $user = $order->getUser();
        if (!$user || !$user->getEmail()) {
            return;
        }
        $firstName = htmlspecialchars($user->getFirstName() ?? 'client', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $email = (new Email())
            ->from($this->fromEmail)
            ->to($user->getEmail())
            ->subject('Retour du matériel prêté - Vite Gourmand')
            ->html("
                <p>Bonjour {$firstName},</p>
                <p>Votre commande est désormais au statut <strong>en attente du retour de matériel</strong>.</p>
                <p>Le matériel prêté doit être restitué.</p>
                <p>Si le matériel n’est pas restitué sous 10 jours ouvrés, des frais de 600 euros pourront être appliqués, conformément aux conditions générales de vente.</p>
                <p>Pour organiser le retour du matériel, merci de prendre contact avec l'équipe.</p>
                <p>Cordialement,<br>Vite Gourmand</p>
            ");
        $this->mailer->send($email);
    }
}
