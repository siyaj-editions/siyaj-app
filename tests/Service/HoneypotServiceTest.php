<?php

namespace App\Tests\Service;

use App\Service\HoneypotService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormInterface;

class HoneypotServiceTest extends TestCase
{
    public function testReturnsFalseWhenFieldDoesNotExist(): void
    {
        $form = $this->createMock(FormInterface::class);
        $form->method('has')->with('company')->willReturn(false);

        $service = new HoneypotService();

        self::assertFalse($service->isTriggered($form));
    }

    public function testReturnsTrueWhenFieldContainsAValue(): void
    {
        $field = $this->createMock(FormInterface::class);
        $field->method('getData')->willReturn('bot-value');

        $form = $this->createMock(FormInterface::class);
        $form->method('has')->with('company')->willReturn(true);
        $form->method('get')->with('company')->willReturn($field);

        $service = new HoneypotService();

        self::assertTrue($service->isTriggered($form));
    }
}
