<?php

namespace App\Service;

use App\Model\ContactMessage;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class ContactService
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly string $contactEmail
    ) {
    }

    public function send(ContactMessage $contactMessage): void
    {
        $senderEmail = mb_strtolower(trim((string) $contactMessage->getEmail()));
        $senderName = $contactMessage->getFullName();

        $email = (new TemplatedEmail())
            ->from(new Address($this->contactEmail, 'SIYAJ Editions'))
            ->to(new Address($this->contactEmail, 'SIYAJ Editions'))
            ->replyTo(new Address($senderEmail, $senderName !== '' ? $senderName : $senderEmail))
            ->subject(sprintf('[Contact] %s', $this->labelForSubject((string) $contactMessage->getSubject())))
            ->htmlTemplate('emails/contact.html.twig')
            ->context([
                'contactMessage' => $contactMessage,
                'subjectLabel' => $this->labelForSubject((string) $contactMessage->getSubject()),
            ]);

        $this->mailer->send($email);
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
