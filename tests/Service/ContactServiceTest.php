<?php

namespace App\Tests\Service;

use App\Model\ContactMessage;
use App\Service\ContactService;
use App\Service\MailConfiguration;
use App\Service\NotificationMailer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\RawMessage;

class ContactServiceTest extends TestCase
{
    public function testSendBuildsEmailWithSubjectAndBody(): void
    {
        $mailer = $this->createMock(MailerInterface::class);
        $contactMessage = (new ContactMessage())
            ->setFirstname('Ludwig')
            ->setLastname('Elatre')
            ->setEmail('USER@MAIL.COM')
            ->setSubject('communication')
            ->setMessage('Bonjour, ceci est un message de test suffisamment long.');

        $mailer
            ->expects(self::once())
            ->method('send')
            ->with(self::callback(function (RawMessage $message): bool {
                self::assertInstanceOf(Email::class, $message);
                self::assertSame('[Contact] Communication', $message->getSubject());
                self::assertSame('SIYAJ Editions', $message->getTo()[0]->getName());
                self::assertSame('contact@siyaj-editions.fr', $message->getTo()[0]->getAddress());
                self::assertSame('SIYAJ Editions', $message->getFrom()[0]->getName());
                self::assertSame('noreply@siyaj-editions.fr', $message->getFrom()[0]->getAddress());
                self::assertStringContainsString('Nom : Ludwig Elatre', (string) $message->getTextBody());
                self::assertStringContainsString('Email : USER@MAIL.COM', (string) $message->getTextBody());
                self::assertStringContainsString('Message :', (string) $message->getTextBody());

                return true;
            }));

        $notificationMailer = new NotificationMailer(
            $mailer,
            new MailConfiguration('noreply@siyaj-editions.fr', 'contact@siyaj-editions.fr')
        );

        $service = new ContactService($notificationMailer);
        $service->send($contactMessage);
    }
}
