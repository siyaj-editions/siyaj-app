<?php

namespace App\Service;

use App\Model\ContactMessage;

class ContactService
{
    public function __construct(
        private readonly NotificationMailer $notificationMailer,
    ) {
    }

    public function send(ContactMessage $contactMessage): void
    {
        $subjectLabel = $this->labelForSubject((string) $contactMessage->getSubject());

        $email = $this->notificationMailer
            ->newAdminEmail('SIYAJ Editions', 'SIYAJ Editions')
            ->subject(sprintf('[Contact] %s', $subjectLabel))
            ->text($this->buildPlainTextBody($contactMessage, $subjectLabel));

        $this->notificationMailer->send($email);
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
