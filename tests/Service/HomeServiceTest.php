<?php

namespace App\Tests\Service;

use App\Entity\Newsletter;
use App\Repository\BookRepository;
use App\Repository\NewsletterRepository;
use App\Service\HomeService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class HomeServiceTest extends TestCase
{
    public function testGetLatestActiveBooksDelegatesToRepository(): void
    {
        $bookRepository = $this->createMock(BookRepository::class);
        $newsletterRepository = $this->createMock(NewsletterRepository::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);

        $expectedBooks = [new \stdClass()];
        $bookRepository
            ->expects(self::once())
            ->method('findBy')
            ->with(['isActive' => true], ['publishedAt' => 'DESC'], 6)
            ->willReturn($expectedBooks);

        $service = new HomeService($bookRepository, $newsletterRepository, $entityManager);

        self::assertSame($expectedBooks, $service->getLatestActiveBooks());
    }

    public function testSubscribeToNewsletterReturnsWarningWhenAlreadyExists(): void
    {
        $bookRepository = $this->createMock(BookRepository::class);
        $newsletterRepository = $this->createMock(NewsletterRepository::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);

        $newsletter = (new Newsletter())->setEmail('USER@MAIL.COM');

        $newsletterRepository
            ->expects(self::once())
            ->method('findOneBy')
            ->with(['email' => 'user@mail.com'])
            ->willReturn(new Newsletter());

        $entityManager->expects(self::never())->method('persist');
        $entityManager->expects(self::never())->method('flush');

        $service = new HomeService($bookRepository, $newsletterRepository, $entityManager);
        $result = $service->subscribeToNewsletter($newsletter);

        self::assertSame('warning', $result->flashType);
    }

    public function testSubscribeToNewsletterPersistsWhenEmailIsNew(): void
    {
        $bookRepository = $this->createMock(BookRepository::class);
        $newsletterRepository = $this->createMock(NewsletterRepository::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);

        $newsletter = (new Newsletter())->setEmail('  USER@MAIL.COM  ');

        $newsletterRepository
            ->expects(self::once())
            ->method('findOneBy')
            ->with(['email' => 'user@mail.com'])
            ->willReturn(null);

        $entityManager->expects(self::once())->method('persist')->with($newsletter);
        $entityManager->expects(self::once())->method('flush');

        $service = new HomeService($bookRepository, $newsletterRepository, $entityManager);
        $result = $service->subscribeToNewsletter($newsletter);

        self::assertSame('success', $result->flashType);
        self::assertSame('user@mail.com', $newsletter->getEmail());
    }
}
