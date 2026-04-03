<?php

namespace App\Controller;

use App\Entity\User;
use App\Exception\CheckoutException;
use App\Form\CheckoutType;
use App\Service\CartService;
use App\Service\CheckoutFlowService;
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
        private readonly CartService $cartService,
        private readonly CheckoutService $checkoutService,
        private readonly CheckoutFlowService $checkoutFlowService,
        private readonly StripeService $stripeService
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
        $shippingPreviewMap = $this->checkoutService->buildShippingPreviewMap($addresses);
        $defaultShippingMethod = $defaultAddressId !== null && isset($shippingPreviewMap[$defaultAddressId])
            ? (string) $shippingPreviewMap[$defaultAddressId]['defaultMethod']
            : 'delivery';
        $formData = [
            'shippingAddressId' => $defaultAddressId,
            'shippingMethod' => $defaultShippingMethod,
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
            'itemsSubtotalCents' => $this->cartService->getTotalCents(),
            'addresses' => $addresses,
            'shippingPreviewMap' => $shippingPreviewMap,
        ]);
    }

    #[Route('/start', name: 'app_checkout_start', methods: ['GET'])]
    public function start(Request $request): Response
    {
        $sessionId = $request->query->get('session_id');
        if (!is_string($sessionId) || $sessionId === '') {
            return $this->redirectToRoute('app_cart');
        }

        return $this->render(
            'checkout/start.html.twig',
            $this->checkoutFlowService->buildStartViewData($sessionId, $this->getParameter('stripe_public_key'))
        );
    }

    #[Route('/success', name: 'app_checkout_success')]
    public function success(Request $request): Response
    {
        $sessionId = $request->query->get('session_id');

        if (!is_string($sessionId) || $sessionId === '') {
            return $this->redirectToRoute('app_home');
        }

        /** @var User $user */
        $user = $this->getUser();
        $result = $this->checkoutFlowService->handleSuccess($sessionId, $user);

        if (!$result->stripeSessionFound) {
            $this->addFlash('error', 'Session Stripe introuvable (clé incorrecte ou session expirée).');
        }

        if (!$result->order) {
            throw $this->createNotFoundException('Commande non trouvée.');
        }

        return $this->render('checkout/success.html.twig', [
            'order' => $result->order,
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

        if (!is_string($sessionId) || $sessionId === '') {
            return new JsonResponse(['error' => 'session_id manquant'], 400);
        }

        $sessionData = $this->checkoutFlowService->getDebugSessionData($sessionId);
        if (!$sessionData) {
            return new JsonResponse(['error' => 'Session Stripe introuvable'], 404);
        }

        return new JsonResponse($sessionData);
    }

}
