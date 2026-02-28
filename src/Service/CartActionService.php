<?php

namespace App\Service;

use App\Repository\BookRepository;

class CartActionService
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly BookRepository $bookRepository
    ) {
    }

    public function addBookById(int $bookId): CartActionResult
    {
        $book = $this->bookRepository->find($bookId);
        if (!$book) {
            return new CartActionResult('error', 'Livre non trouvé.', 'app_catalog');
        }

        if (!$book->isActive()) {
            return new CartActionResult('error', 'Ce livre n\'est plus disponible.', 'app_catalog');
        }

        if (!$book->isInStock()) {
            return new CartActionResult('error', 'Ce livre est en rupture de stock.', 'app_book_show', [
                'slug' => $book->getSlug(),
            ]);
        }

        $this->cartService->add($book);

        return new CartActionResult('success', 'Le livre a été ajouté au panier.', 'app_cart');
    }

    public function incrementBookById(int $bookId): CartActionResult
    {
        $book = $this->bookRepository->find($bookId);
        if (!$book) {
            return new CartActionResult('error', 'Livre non trouvé.', 'app_cart');
        }

        if ($this->isStockExceeded($bookId, $book->getStock())) {
            return new CartActionResult('error', 'Stock insuffisant pour ce livre.', 'app_cart');
        }

        $this->cartService->increment($bookId);

        return new CartActionResult(null, null, 'app_cart');
    }

    private function isStockExceeded(int $bookId, ?int $stock): bool
    {
        if ($stock === null) {
            return false;
        }

        $cart = $this->cartService->getFullCart();
        $currentQuantity = 0;
        foreach ($cart as $item) {
            if ($item['book']->getId() === $bookId) {
                $currentQuantity = $item['quantity'];
                break;
            }
        }

        return $currentQuantity + 1 > $stock;
    }
}
