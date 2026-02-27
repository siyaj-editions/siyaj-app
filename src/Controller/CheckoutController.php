<?php

namespace App\Controller;

use App\Entity\User;
use App\Exception\CheckoutException;
use App\Form\CheckoutType;
use App\Repository\OrderRepository;
use App\Service\CartService;
use App\Service\CheckoutService;
use App\Service\StripeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/checkout')]
#[IsGranted('ROLE_USER')]
class CheckoutController extends AbstractController
{
    public function __construct(
        private CartService $cartService,
        private CheckoutService $checkoutService,
        private StripeService $stripeService,
        private OrderRepository $orderRepository
    ) {
    }

    #[Route('/informations', name: 'app_checkout_information', methods: ['GET', 'POST'])]
    public function information(Request $request): Response
    {
        $cartValidationErrors = $this->checkoutService->getCartValidationErrors();
        if ($cartValidationErrors !== []) {
            foreach ($cartValidationErrors as $error) {
                $this->addFlash('error', $error);
            }

            return $this->redirectToRoute('app_cart');
        }

        /** @var User $user */
        $user = $this->getUser();
        $addresses = $this->checkoutService->getUserAddresses($user);
        if ($addresses === []) {
            $this->addFlash('warning', 'Ajoutez au moins une adresse avant de continuer le paiement.');

            return $this->redirectToRoute('app_account_address');
        }

        $choices = $this->checkoutService->buildAddressChoices($addresses);
        $defaultAddressId = $this->checkoutService->getDefaultAddressId($addresses);
        $formData = [
            'shippingAddressId' => $defaultAddressId,
            'billingSameAsShipping' => true,
            'billingAddressId' => $defaultAddressId,
        ];

        $form = $this->createForm(CheckoutType::class, $formData, [
            'address_choices' => $choices,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $order = $this->checkoutService->createOrderFromFormData($user, $form->getData());
                $session = $this->stripeService->createCheckoutSession($order);

                return $this->redirectToRoute('app_checkout_start', [
                    'session_id' => $session->id,
                ]);
            } catch (CheckoutException $e) {
                $this->addFlash('error', $e->getMessage());

                return $this->redirectToRoute('app_checkout_information');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la création du paiement: ' . $e->getMessage());

                return $this->redirectToRoute('app_cart');
            }
        }

        return $this->render('checkout/information.html.twig', [
            'checkoutForm' => $form,
            'cart' => $this->cartService->getFullCart(),
            'total' => $this->cartService->getTotalEuros(),
            'addresses' => $addresses,
        ]);
    }

    #[Route('/start', name: 'app_checkout_start', methods: ['GET'])]
    public function start(Request $request): Response
    {
        $sessionId = $request->query->get('session_id');
        if (!$sessionId) {
            return $this->redirectToRoute('app_cart');
        }

        $session = $this->stripeService->retrieveSession($sessionId);

        return $this->render('checkout/start.html.twig', [
            'stripePublicKey' => $this->getParameter('stripe_public_key'),
            'sessionId' => $sessionId,
            'sessionUrl' => $session?->url,
        ]);
    }

    #[Route('/success', name: 'app_checkout_success')]
    public function success(Request $request): Response
    {
        $sessionId = $request->query->get('session_id');

        if (!$sessionId) {
            return $this->redirectToRoute('app_home');
        }

        $stripeSession = $this->stripeService->retrieveSession($sessionId);
        if (!$stripeSession) {
            $this->addFlash('error', 'Session Stripe introuvable (clé incorrecte ou session expirée).');
        } else {
            $this->stripeService->syncPaidOrderFromSession($stripeSession);
        }

        $order = $this->orderRepository->findByStripeSessionId($sessionId);

        if (!$order || $order->getUser() !== $this->getUser()) {
            throw $this->createNotFoundException('Commande non trouvée.');
        }

        if ($order->isPaid()) {
            $this->cartService->clear();
        }

        return $this->render('checkout/success.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('/cancel', name: 'app_checkout_cancel')]
    public function cancel(): Response
    {
        $this->addFlash('warning', 'Le paiement a été annulé.');

        return $this->render('checkout/cancel.html.twig');
    }

    #[Route('/debug', name: 'app_checkout_debug', methods: ['GET'])]
    public function debug(Request $request): JsonResponse
    {
        $sessionId = $request->query->get('session_id');

        if (!$sessionId) {
            return new JsonResponse(['error' => 'session_id manquant'], 400);
        }

        $session = $this->stripeService->retrieveSession($sessionId);
        if (!$session) {
            return new JsonResponse(['error' => 'Session Stripe introuvable'], 404);
        }

        return new JsonResponse([
            'id' => $session->id ?? null,
            'status' => $session->status ?? null,
            'payment_status' => $session->payment_status ?? null,
            'livemode' => $session->livemode ?? null,
            'expires_at' => $session->expires_at ?? null,
            'url' => $session->url ?? null,
        ]);
    }

}
