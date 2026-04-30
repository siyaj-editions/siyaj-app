<?php

namespace App\Service;

use App\Entity\Order;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class OrderNotificationService
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly string $mailerFromEmail,
        private readonly string $contactEmail,
    ) {
    }

    public function sendShipmentNotification(Order $order): void
    {
        $user = $order->getUser();
        $userEmail = $user?->getEmail();

        if (!$userEmail) {
            return;
        }

        $email = (new TemplatedEmail())
            ->from(new Address($this->mailerFromEmail, 'SIYAJ Éditions'))
            ->to(new Address($userEmail, $user->getFullName()))
            ->subject(sprintf('Votre commande %s a été expédiée', $order->getReference()))
            ->htmlTemplate('emails/order_shipped.html.twig')
            ->textTemplate('emails/order_shipped.txt.twig')
            ->context([
                'order' => $order,
                'orderUrl' => $this->urlGenerator->generate('app_account_order_show', ['id' => $order->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
            ]);

        $this->mailer->send($email);
    }

    public function sendPaidOrderAdminNotification(Order $order): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address($this->mailerFromEmail, 'SIYAJ Éditions'))
            ->to(new Address($this->contactEmail, 'Administration SIYAJ'))
            ->subject(sprintf('Nouvelle commande payée : %s', $order->getReference()))
            ->htmlTemplate('emails/order_paid_admin.html.twig')
            ->textTemplate('emails/order_paid_admin.txt.twig')
            ->context([
                'order' => $order,
                'adminOrderUrl' => $this->urlGenerator->generate('app_admin_order_show', ['id' => $order->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
            ]);

        $this->mailer->send($email);
    }
}
