<?php

namespace App\Service;

use App\Entity\Order;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
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
        $email = $this->newCustomerTemplatedEmail(
            $order,
            sprintf('Votre commande %s a été expédiée', $order->getReference())
        );

        if ($email === null) {
            return;
        }

        $email
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
            ->newAdminTemplatedEmail('SIYAJ Éditions', 'Administration SIYAJ')
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
        $email = $this->newCustomerEmail(
            $order,
            sprintf('Nous avons bien reçu ta commande #%s', $order->getReference())
        );

        if ($email === null) {
            return;
        }

        $email->text(implode("\n", [
                sprintf('Merci d’avoir passé commande sur le site Siyaj-Editions.com. Ta commande #%s est en cours de traitement.', $order->getReference()),
                'Tu peux la suivre directement dans ton espace lecture. Nous reviendrons vers toi quand elle sera prête.',
                '',
                'L’équipe Siyaj Editions',
            ]));

        $this->notificationMailer->send($email);
    }

    public function sendReadyForPickupNotification(Order $order): void
    {
        $email = $this->newCustomerEmail(
            $order,
            sprintf('Ta commande %s est prête à être retirée', $order->getReference())
        );

        if ($email === null) {
            return;
        }

        $email->text(implode("\n", [
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

    private function newCustomerEmail(Order $order, string $subject): ?Email
    {
        $recipient = $this->customerAddress($order);

        if ($recipient === null) {
            return null;
        }

        return $this->notificationMailer
            ->newEmail('SIYAJ Éditions')
            ->to($recipient)
            ->subject($subject);
    }

    private function newCustomerTemplatedEmail(Order $order, string $subject): ?TemplatedEmail
    {
        $recipient = $this->customerAddress($order);

        if ($recipient === null) {
            return null;
        }

        return $this->notificationMailer
            ->newTemplatedEmail('SIYAJ Éditions')
            ->to($recipient)
            ->subject($subject);
    }

    private function customerAddress(Order $order): ?Address
    {
        $user = $order->getUser();
        $userEmail = $user?->getEmail();

        if (!$userEmail) {
            return null;
        }

        return $this->notificationMailer->recipientAddress($userEmail, $user->getFullName());
    }
}
