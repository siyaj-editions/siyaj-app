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

    public function send(RawMessage $message): void
    {
        $this->mailer->send($message);
    }
}
