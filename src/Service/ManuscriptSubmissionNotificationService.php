<?php

namespace App\Service;

use App\Entity\ManuscriptSubmission;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ManuscriptSubmissionNotificationService
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly string $mailerFromEmail,
        private readonly string $contactEmail,
    ) {
    }

    public function sendAdminNotification(ManuscriptSubmission $submission): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address($this->mailerFromEmail, 'SIYAJ Éditions'))
            ->to(new Address($this->contactEmail, 'Administration SIYAJ'))
            ->subject(sprintf('Nouveau manuscrit reçu : %s', (string) $submission->getBookTitle()))
            ->htmlTemplate('emails/manuscript_submission_admin.html.twig')
            ->textTemplate('emails/manuscript_submission_admin.txt.twig')
            ->context([
                'submission' => $submission,
                'adminManuscriptUrl' => $this->urlGenerator->generate('app_admin_manuscript_index', [], UrlGeneratorInterface::ABSOLUTE_URL),
            ]);

        $this->mailer->send($email);
    }
}
