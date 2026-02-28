<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationService
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function registerUser(User $user, string $plainPassword, UserPasswordHasherInterface $userPasswordHasher): void
    {
        $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
