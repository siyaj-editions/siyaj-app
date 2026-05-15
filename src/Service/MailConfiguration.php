<?php

namespace App\Service;

use Symfony\Component\Mime\Address;

final class MailConfiguration
{
    public function __construct(
        private readonly string $mailerFromEmail,
        private readonly string $contactEmail,
    ) {
    }

    public function fromAddress(string $name = 'SIYAJ Éditions'): Address
    {
        return new Address($this->mailerFromEmail, $name);
    }

    public function adminAddress(string $name = 'Administration SIYAJ'): Address
    {
        return new Address($this->contactEmail, $name);
    }
}
