<?php

namespace App\Controller;

use App\Service\CartActionService;
use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/panier')]
class CartController extends AbstractController
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly CartActionService $cartActionService
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
        $result = $this->cartActionService->addBookById($id);
        if ($result->flashType && $result->flashMessage) {
            $this->addFlash($result->flashType, $result->flashMessage);
        }

        return $this->redirectToRoute($result->redirectRoute, $result->routeParameters);
    }

    #[Route('/increment/{id}', name: 'app_cart_increment', methods: ['POST'])]
    public function increment(int $id): Response
    {
        $result = $this->cartActionService->incrementBookById($id);
        if ($result->flashType && $result->flashMessage) {
            $this->addFlash($result->flashType, $result->flashMessage);
        }

        return $this->redirectToRoute($result->redirectRoute, $result->routeParameters);
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
