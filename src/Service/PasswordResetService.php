<?php

namespace App\Service;

use App\Entity\PasswordResetCode;
use App\Entity\User;
use App\Repository\PasswordResetCodeRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PasswordResetService
{
    private const CODE_TTL_MINUTES = 15;
    private const MAX_ATTEMPTS = 5;

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly PasswordResetCodeRepository $passwordResetCodeRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly MailerInterface $mailer,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly string $mailerFromEmail,
        private readonly string $appSecret,
    ) {
    }

    public function requestReset(string $email): void
    {
        $normalizedEmail = $this->normalizeEmail($email);
        $user = $this->userRepository->findOneBy(['email' => $normalizedEmail]);

        if (!$user instanceof User) {
            return;
        }

        $this->invalidateActiveCodes($user);

        $plainCode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $resetCode = (new PasswordResetCode())
            ->setUser($user)
            ->setCodeHash($this->hashCode($plainCode))
            ->setExpiresAt(new \DateTimeImmutable(sprintf('+%d minutes', self::CODE_TTL_MINUTES)));

        $this->entityManager->persist($resetCode);
        $this->entityManager->flush();

        $emailMessage = (new Email())
            ->from(new Address($this->mailerFromEmail, 'SIYAJ Editions'))
            ->to(new Address($normalizedEmail))
            ->subject('Ton code de réinitialisation SIYAJ')
            ->text(implode("\n", [
                'Bonjour,',
                '',
                'Voici ton code de réinitialisation : '.$plainCode,
                '',
                sprintf('Ce code expire dans %d minutes.', self::CODE_TTL_MINUTES),
                'Si tu n’es pas à l’origine de cette demande, tu peux ignorer ce message.',
            ]));

        $this->mailer->send($emailMessage);
    }

    public function verifyCodeAndResetPassword(string $email, string $code, string $newPassword): string
    {
        $normalizedEmail = $this->normalizeEmail($email);
        $user = $this->userRepository->findOneBy(['email' => $normalizedEmail]);

        if (!$user instanceof User) {
            return 'invalid';
        }

        $resetCode = $this->passwordResetCodeRepository->findLatestActiveCodeForUser($user);
        $now = new \DateTimeImmutable();

        if (!$resetCode instanceof PasswordResetCode) {
            return 'expired';
        }

        if ($resetCode->isExpiredAt($now)) {
            $resetCode->setUsedAt($now);
            $this->entityManager->flush();

            return 'expired';
        }

        if ($resetCode->getAttempts() >= self::MAX_ATTEMPTS) {
            $resetCode->setUsedAt($now);
            $this->entityManager->flush();

            return 'locked';
        }

        if (!hash_equals((string) $resetCode->getCodeHash(), $this->hashCode($code))) {
            $resetCode->incrementAttempts();

            if ($resetCode->getAttempts() >= self::MAX_ATTEMPTS) {
                $resetCode->setUsedAt($now);
            }

            $this->entityManager->flush();

            return 'invalid';
        }

        $user->setPassword($this->passwordHasher->hashPassword($user, $newPassword));
        $resetCode->setUsedAt($now);

        $this->entityManager->flush();

        return 'success';
    }

    private function invalidateActiveCodes(User $user): void
    {
        $activeCode = $this->passwordResetCodeRepository->findLatestActiveCodeForUser($user);

        if ($activeCode instanceof PasswordResetCode && $activeCode->getUsedAt() === null) {
            $activeCode->setUsedAt(new \DateTimeImmutable());
        }
    }

    private function normalizeEmail(string $email): string
    {
        return mb_strtolower(trim($email));
    }

    private function hashCode(string $code): string
    {
        return hash_hmac('sha256', trim($code), $this->appSecret);
    }
}
