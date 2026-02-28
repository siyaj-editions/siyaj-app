<?php

namespace App\Tests\Service;

use App\Entity\Book;
use App\Repository\BookRepository;
use App\Service\CartActionService;
use App\Service\CartService;
use PHPUnit\Framework\TestCase;

class CartActionServiceTest extends TestCase
{
    public function testAddBookByIdReturnsErrorWhenBookNotFound(): void
    {
        $cartService = $this->createMock(CartService::class);
        $bookRepository = $this->createMock(BookRepository::class);

        $bookRepository->method('find')->with(123)->willReturn(null);

        $service = new CartActionService($cartService, $bookRepository);
        $result = $service->addBookById(123);

        self::assertSame('error', $result->flashType);
        self::assertSame('app_catalog', $result->redirectRoute);
    }

    public function testAddBookByIdAddsBookWhenValid(): void
    {
        $cartService = $this->createMock(CartService::class);
        $bookRepository = $this->createMock(BookRepository::class);

        $book = (new Book())
            ->setTitle('Livre test')
            ->setSlug('livre-test')
            ->setStock(5)
            ->setIsActive(true)
            ->setPriceCents(1000);

        $bookRepository->method('find')->with(1)->willReturn($book);
        $cartService->expects(self::once())->method('add')->with($book);

        $service = new CartActionService($cartService, $bookRepository);
        $result = $service->addBookById(1);

        self::assertSame('success', $result->flashType);
        self::assertSame('app_cart', $result->redirectRoute);
    }

    public function testIncrementBookByIdReturnsErrorWhenStockExceeded(): void
    {
        $cartService = $this->createMock(CartService::class);
        $bookRepository = $this->createMock(BookRepository::class);

        $book = $this->createMock(Book::class);
        $book->method('getId')->willReturn(1);
        $book->method('getStock')->willReturn(1);

        $bookRepository->method('find')->with(1)->willReturn($book);
        $cartService->method('getFullCart')->willReturn([
            ['book' => $book, 'quantity' => 1],
        ]);
        $cartService->expects(self::never())->method('increment');

        $service = new CartActionService($cartService, $bookRepository);
        $result = $service->incrementBookById(1);

        self::assertSame('error', $result->flashType);
        self::assertSame('app_cart', $result->redirectRoute);
    }
}
