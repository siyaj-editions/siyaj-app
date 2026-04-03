<?php

namespace App\Service;

use Symfony\Component\Form\FormInterface;

class HoneypotService
{
    public function isTriggered(FormInterface $form, string $field = 'company'): bool
    {
        if (!$form->has($field)) {
            return false;
        }

        return trim((string) $form->get($field)->getData()) !== '';
    }
}
