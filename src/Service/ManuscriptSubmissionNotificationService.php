<?php

namespace App\Service;

use App\Entity\ManuscriptSubmission;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ManuscriptSubmissionNotificationService
{
    public function __construct(
        private readonly NotificationMailer $notificationMailer,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function sendAdminNotification(ManuscriptSubmission $submission): void
    {
        $email = $this->notificationMailer
            ->newAdminTemplatedEmail('SIYAJ Éditions', 'Administration SIYAJ')
            ->subject(sprintf('Nouveau manuscrit reçu : %s', (string) $submission->getBookTitle()))
            ->htmlTemplate('emails/manuscript_submission_admin.html.twig')
            ->textTemplate('emails/manuscript_submission_admin.txt.twig')
            ->context([
                'submission' => $submission,
                'adminManuscriptUrl' => $this->urlGenerator->generate('app_admin_manuscript_index', [], UrlGeneratorInterface::ABSOLUTE_URL),
            ]);

        $this->notificationMailer->send($email);
    }
}
