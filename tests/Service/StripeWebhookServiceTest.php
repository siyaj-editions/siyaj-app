<?php

namespace App\Tests\Service;

use App\Service\StripeService;
use App\Service\StripeWebhookService;
use PHPUnit\Framework\TestCase;

class StripeWebhookServiceTest extends TestCase
{
    public function testHandleWebhookPayloadThrowsWhenSignatureMissing(): void
    {
        $stripeService = $this->createMock(StripeService::class);
        $stripeService->expects(self::never())->method('handleWebhook');

        $service = new StripeWebhookService($stripeService);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No signature');

        $service->handleWebhookPayload('{"id":"evt_123"}', null);
    }

    public function testHandleWebhookPayloadDelegatesToStripeService(): void
    {
        $stripeService = $this->createMock(StripeService::class);
        $stripeService
            ->expects(self::once())
            ->method('handleWebhook')
            ->with('{"id":"evt_123"}', 't=1,v1=abc');

        $service = new StripeWebhookService($stripeService);
        $service->handleWebhookPayload('{"id":"evt_123"}', 't=1,v1=abc');
    }
}
