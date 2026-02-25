<?php

namespace App\Service;

use App\Entity\Book;
use App\Repository\BookRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class CartService
{
    private const CART_SESSION_KEY = 'cart';

    public function __construct(
        private RequestStack $requestStack,
        private BookRepository $bookRepository
    ) {
    }

    /**
     * Ajoute un livre au panier
     */
    public function add(Book $book, int $quantity = 1): void
    {
        $cart = $this->getCart();
        $bookId = $book->getId();

        if (isset($cart[$bookId])) {
            $cart[$bookId]['quantity'] += $quantity;
        } else {
            $cart[$bookId] = [
                'quantity' => $quantity,
            ];
        }

        $this->saveCart($cart);
    }

    /**
     * Supprime un livre du panier
     */
    public function remove(int $bookId): void
    {
        $cart = $this->getCart();

        if (isset($cart[$bookId])) {
            unset($cart[$bookId]);
        }

        $this->saveCart($cart);
    }

    /**
     * Incrémente la quantité d'un livre
     */
    public function increment(int $bookId): void
    {
        $cart = $this->getCart();

        if (isset($cart[$bookId])) {
            $cart[$bookId]['quantity']++;
        }

        $this->saveCart($cart);
    }

    /**
     * Décrémente la quantité d'un livre
     */
    public function decrement(int $bookId): void
    {
        $cart = $this->getCart();

        if (isset($cart[$bookId])) {
            $cart[$bookId]['quantity']--;

            if ($cart[$bookId]['quantity'] <= 0) {
                unset($cart[$bookId]);
            }
        }

        $this->saveCart($cart);
    }

    /**
     * Vide le panier
     */
    public function clear(): void
    {
        $this->saveCart([]);
    }

    /**
     * Récupère le panier avec les détails complets
     */
    public function getFullCart(): array
    {
        $cart = $this->getCart();
        $fullCart = [];

        foreach ($cart as $bookId => $item) {
            $book = $this->bookRepository->find($bookId);

            if ($book) {
                $fullCart[] = [
                    'book' => $book,
                    'quantity' => $item['quantity'],
                ];
            }
        }

        return $fullCart;
    }

    /**
     * Calcule le total du panier en centimes
     */
    public function getTotalCents(): int
    {
        $total = 0;
        $fullCart = $this->getFullCart();

        foreach ($fullCart as $item) {
            $total += $item['book']->getPriceCents() * $item['quantity'];
        }

        return $total;
    }

    /**
     * Calcule le total du panier en euros
     */
    public function getTotalEuros(): float
    {
        return $this->getTotalCents() / 100;
    }

    /**
     * Compte le nombre total d'articles dans le panier
     */
    public function getItemCount(): int
    {
        $cart = $this->getCart();
        $count = 0;

        foreach ($cart as $item) {
            $count += $item['quantity'];
        }

        return $count;
    }

    /**
     * Vérifie si le panier est vide
     */
    public function isEmpty(): bool
    {
        return empty($this->getCart());
    }

    /**
     * Récupère le panier brut depuis la session
     */
    private function getCart(): array
    {
        $session = $this->requestStack->getSession();
        return $session->get(self::CART_SESSION_KEY, []);
    }

    /**
     * Sauvegarde le panier dans la session
     */
    private function saveCart(array $cart): void
    {
        $session = $this->requestStack->getSession();
        $session->set(self::CART_SESSION_KEY, $cart);
    }

    /**
     * Valide la disponibilité des stocks avant le paiement
     */
    public function validateStock(): array
    {
        $errors = [];
        $fullCart = $this->getFullCart();

        foreach ($fullCart as $item) {
            $book = $item['book'];
            $quantity = $item['quantity'];

            if (!$book->isActive()) {
                $errors[] = "Le livre \"{$book->getTitle()}\" n'est plus disponible.";
            } elseif (!$book->isInStock()) {
                $errors[] = "Le livre \"{$book->getTitle()}\" est en rupture de stock.";
            } elseif ($book->getStock() !== null && $book->getStock() < $quantity) {
                $errors[] = "Stock insuffisant pour \"{$book->getTitle()}\" (disponible: {$book->getStock()}).";
            }
        }

        return $errors;
    }
}
