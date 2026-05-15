<?php

namespace App\Service;

use App\Entity\Order;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class OrderNotificationService
{
    public function __construct(
        private readonly NotificationMailer $notificationMailer,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function sendShipmentNotification(Order $order): void
    {
        $user = $order->getUser();
        $userEmail = $user?->getEmail();

        if (!$userEmail) {
            return;
        }

        $email = $this->notificationMailer
            ->newTemplatedEmail('SIYAJ Éditions')
            ->to(new Address($userEmail, $user->getFullName()))
            ->subject(sprintf('Votre commande %s a été expédiée', $order->getReference()))
            ->htmlTemplate('emails/order_shipped.html.twig')
            ->textTemplate('emails/order_shipped.txt.twig')
            ->context([
                'order' => $order,
                'orderUrl' => $this->urlGenerator->generate('app_account_order_show', ['id' => $order->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
            ]);

        $this->notificationMailer->send($email);
    }

    public function sendPaidOrderAdminNotification(Order $order): void
    {
        $email = $this->notificationMailer
            ->newTemplatedEmail('SIYAJ Éditions')
            ->to($this->notificationMailer->adminAddress('Administration SIYAJ'))
            ->subject(sprintf('Nouvelle commande payée : %s', $order->getReference()))
            ->htmlTemplate('emails/order_paid_admin.html.twig')
            ->textTemplate('emails/order_paid_admin.txt.twig')
            ->context([
                'order' => $order,
                'adminOrderUrl' => $this->urlGenerator->generate('app_admin_order_show', ['id' => $order->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
            ]);

        $this->notificationMailer->send($email);
    }

    public function sendPaidOrderCustomerNotification(Order $order): void
    {
        $user = $order->getUser();
        $userEmail = $user?->getEmail();

        if (!$userEmail) {
            return;
        }

        $email = $this->notificationMailer
            ->newEmail('SIYAJ Éditions')
            ->to(new Address($userEmail, $user->getFullName()))
            ->subject(sprintf('Nous avons bien reçu ta commande #%s', $order->getReference()))
            ->text(implode("\n", [
                sprintf('Merci d’avoir passé commande sur le site Siyaj-Editions.com. Ta commande #%s est en cours de traitement.', $order->getReference()),
                'Tu peux la suivre directement dans ton espace lecture. Nous reviendrons vers toi quand elle sera prête.',
                '',
                'L’équipe Siyaj Editions',
            ]));

        $this->notificationMailer->send($email);
    }

    public function sendReadyForPickupNotification(Order $order): void
    {
        $user = $order->getUser();
        $userEmail = $user?->getEmail();

        if (!$userEmail) {
            return;
        }

        $email = $this->notificationMailer
            ->newEmail('SIYAJ Éditions')
            ->to(new Address($userEmail, $user->getFullName()))
            ->subject(sprintf('Ta commande %s est prête à être retirée', $order->getReference()))
            ->text(implode("\n", [
                'Bonjour,',
                '',
                'Merci beaucoup pour ta commande 🫶🏾 nous avons le plaisir de t’informer qu’elle est désormais disponible au Salon de Tatouage Le Temple Tattoo. N’hésite pas à prendre contact avec Oya en DM sur son compte Instagram: @inked.by.oya pour la récupérer sur ses horaires d’ouverture les mardi, mercredi, vendredi et samedi entre 10h à 16h.',
                '',
                'En te souhaitant une belle escapade littéraire,',
                '',
                'L’Equipe Siyaj',
            ]));

        $this->notificationMailer->send($email);
    }
}
