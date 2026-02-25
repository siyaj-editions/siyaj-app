<?php

namespace App\Controller;

use App\Repository\BookRepository;
use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/panier')]
class CartController extends AbstractController
{
    public function __construct(
        private CartService $cartService,
        private BookRepository $bookRepository
    ) {
    }

    #[Route('', name: 'app_cart')]
    public function index(): Response
    {
        $cart = $this->cartService->getFullCart();
        $total = $this->cartService->getTotalEuros();

        return $this->render('cart/index.html.twig', [
            'cart' => $cart,
            'total' => $total,
        ]);
    }

    #[Route('/add/{id}', name: 'app_cart_add', methods: ['POST'])]
    public function add(int $id): Response
    {
        $book = $this->bookRepository->find($id);

        if (!$book) {
            $this->addFlash('error', 'Livre non trouvé.');
            return $this->redirectToRoute('app_catalog');
        }

        if (!$book->isActive()) {
            $this->addFlash('error', 'Ce livre n\'est plus disponible.');
            return $this->redirectToRoute('app_catalog');
        }

        if (!$book->isInStock()) {
            $this->addFlash('error', 'Ce livre est en rupture de stock.');
            return $this->redirectToRoute('app_book_show', ['slug' => $book->getSlug()]);
        }

        $this->cartService->add($book);
        $this->addFlash('success', 'Le livre a été ajouté au panier.');

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/increment/{id}', name: 'app_cart_increment', methods: ['POST'])]
    public function increment(int $id): Response
    {
        $book = $this->bookRepository->find($id);

        if (!$book) {
            $this->addFlash('error', 'Livre non trouvé.');
            return $this->redirectToRoute('app_cart');
        }

        // Vérifier le stock avant d'incrémenter
        $cart = $this->cartService->getFullCart();
        $currentQuantity = 0;

        foreach ($cart as $item) {
            if ($item['book']->getId() === $id) {
                $currentQuantity = $item['quantity'];
                break;
            }
        }

        if ($book->getStock() !== null && $currentQuantity + 1 > $book->getStock()) {
            $this->addFlash('error', 'Stock insuffisant pour ce livre.');
            return $this->redirectToRoute('app_cart');
        }

        $this->cartService->increment($id);

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/decrement/{id}', name: 'app_cart_decrement', methods: ['POST'])]
    public function decrement(int $id): Response
    {
        $this->cartService->decrement($id);

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/remove/{id}', name: 'app_cart_remove', methods: ['POST'])]
    public function remove(int $id): Response
    {
        $this->cartService->remove($id);
        $this->addFlash('success', 'Le livre a été retiré du panier.');

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/clear', name: 'app_cart_clear', methods: ['POST'])]
    public function clear(): Response
    {
        $this->cartService->clear();
        $this->addFlash('success', 'Le panier a été vidé.');

        return $this->redirectToRoute('app_cart');
    }
}
