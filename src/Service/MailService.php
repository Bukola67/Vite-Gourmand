<?php

namespace App\Service;

use App\Entity\CustomerOrder;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailService
{
    public function __construct(
        private MailerInterface $mailer
    ) {
    }

    private string $fromEmail = 'noreply@vitegourmand.local';

    public function sendEquipmentReturnReminder(CustomerOrder $order): void
    {
        $user = $order->getUser();

        if (!$user || !$user->getEmail()) {
            return;
        }

        $firstName = $user->getFirstName() ?? 'client';

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