<?php

namespace App\Service;

use App\Model\ContactMessage;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class ContactService
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly string $contactEmail,
        private readonly string $mailerFromEmail,
    ) {
    }

    public function send(ContactMessage $contactMessage): void
    {
        $subjectLabel = $this->labelForSubject((string) $contactMessage->getSubject());

        $email = (new Email())
            ->from(new Address($this->mailerFromEmail, 'SIYAJ Editions'))
            ->to(new Address($this->contactEmail, 'SIYAJ Editions'))
            ->subject(sprintf('[Contact] %s', $subjectLabel))
            ->text($this->buildPlainTextBody($contactMessage, $subjectLabel));

        $this->mailer->send($email);
    }

    private function buildPlainTextBody(ContactMessage $contactMessage, string $subjectLabel): string
    {
        return implode("\n", [
            'Nouveau message de contact',
            '',
            sprintf('Nom : %s', $contactMessage->getFullName()),
            sprintf('Email : %s', (string) $contactMessage->getEmail()),
            sprintf('Motif : %s', $subjectLabel),
            '',
            'Message :',
            (string) $contactMessage->getMessage(),
        ]);
    }

    private function labelForSubject(string $subject): string
    {
        return match ($subject) {
            'relations_publiques' => 'Relations publiques',
            'communication' => 'Communication',
            'medias' => 'Médias',
            default => 'Autre',
        };
    }
}
