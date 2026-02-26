<?php

namespace App\Controller;

use App\Service\StripeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class StripeWebhookController extends AbstractController
{
    public function __construct(
        private StripeService $stripeService
    ) {
    }

    // Accept both legacy and current webhook paths to avoid local misconfig
    #[Route('/stripe/webhook', name: 'app_stripe_webhook', methods: ['POST'])]
    #[Route('/checkout/webhook', name: 'app_checkout_webhook_legacy', methods: ['POST'])]
    public function webhook(Request $request): Response
    {
        $payload = $request->getContent();
        $signature = $request->headers->get('Stripe-Signature');

        if (!$signature) {
            return new Response('No signature', 400);
        }

        try {
            $this->stripeService->handleWebhook($payload, $signature);

            return new Response('Webhook handled', 200);
        } catch (\Exception $e) {
            return new Response('Webhook error: ' . $e->getMessage(), 400);
        }
    }
}
