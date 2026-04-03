<?php

namespace App\Tests\Service;

use App\Model\ContactMessage;
use App\Service\ContactService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\RawMessage;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class ContactServiceTest extends TestCase
{
    public function testSendBuildsEmailWithReplyToAndSubject(): void
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
                self::assertInstanceOf(TemplatedEmail::class, $message);
                self::assertSame('[Contact] Communication', $message->getSubject());
                self::assertSame('emails/contact.html.twig', $message->getHtmlTemplate());
                self::assertSame('SIYAJ Editions', $message->getTo()[0]->getName());
                self::assertSame('contact@siyaj-editions.fr', $message->getTo()[0]->getAddress());
                self::assertSame('user@mail.com', $message->getReplyTo()[0]->getAddress());
                self::assertSame('Ludwig Elatre', $message->getReplyTo()[0]->getName());

                return true;
            }));

        $service = new ContactService($mailer, 'contact@siyaj-editions.fr');
        $service->send($contactMessage);
    }
}
