<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class StripeWebhookControllerTest extends WebTestCase
{
    public function testStripeWebhookReturns400WithoutSignature(): void
    {
        $client = static::createClient();
        $client->request(
            'POST',
            '/stripe/webhook',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: '{"id":"evt_test"}'
        );

        self::assertResponseStatusCodeSame(400);
    }

    public function testLegacyCheckoutWebhookRouteDoesNotExistAnymore(): void
    {
        $client = static::createClient();
        $client->request(
            'POST',
            '/checkout/webhook',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: '{"id":"evt_test"}'
        );

        self::assertResponseStatusCodeSame(404);
    }
}
