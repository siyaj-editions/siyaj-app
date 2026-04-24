<?php

namespace App\Model;

class PasswordResetVerification
{
    private ?string $email = null;
    private ?string $code = null;
    private ?string $newPassword = null;

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getNewPassword(): ?string
    {
        return $this->newPassword;
    }

    public function setNewPassword(?string $newPassword): static
    {
        $this->newPassword = $newPassword;

        return $this;
    }
}
