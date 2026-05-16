<?php

namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\RawMessage;

final class NotificationMailer
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly MailConfiguration $mailConfiguration,
    ) {
    }

    public function newEmail(string $fromName = 'SIYAJ Éditions'): Email
    {
        return (new Email())
            ->from($this->mailConfiguration->fromAddress($fromName));
    }

    public function newTemplatedEmail(string $fromName = 'SIYAJ Éditions'): TemplatedEmail
    {
        return (new TemplatedEmail())
            ->from($this->mailConfiguration->fromAddress($fromName));
    }

    public function adminAddress(string $name = 'Administration SIYAJ'): Address
    {
        return $this->mailConfiguration->adminAddress($name);
    }

    public function recipientAddress(string $email, ?string $name = null): Address
    {
        return $name !== null && $name !== ''
            ? new Address($email, $name)
            : new Address($email);
    }

    public function newAdminEmail(string $fromName = 'SIYAJ Éditions', string $adminName = 'Administration SIYAJ'): Email
    {
        return $this->newEmail($fromName)
            ->to($this->adminAddress($adminName));
    }

    public function newAdminTemplatedEmail(string $fromName = 'SIYAJ Éditions', string $adminName = 'Administration SIYAJ'): TemplatedEmail
    {
        return $this->newTemplatedEmail($fromName)
            ->to($this->adminAddress($adminName));
    }

    public function send(RawMessage $message): void
    {
        $this->mailer->send($message);
    }
}
