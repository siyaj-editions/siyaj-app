<?php

namespace App\Controller;

use App\Form\PasswordResetRequestType;
use App\Form\PasswordResetVerificationType;
use App\Model\PasswordResetRequest;
use App\Model\PasswordResetVerification;
use App\Service\PasswordResetService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/mot-de-passe-oublie', name: 'app_forgot_password')]
    public function forgotPassword(Request $request, PasswordResetService $passwordResetService): Response
    {
        $passwordResetRequest = new PasswordResetRequest();
        $form = $this->createForm(PasswordResetRequestType::class, $passwordResetRequest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $passwordResetService->requestReset((string) $passwordResetRequest->getEmail());

            $this->addFlash('success', 'Si un compte existe avec cette adresse, un code de réinitialisation a été envoyé.');

            return $this->redirectToRoute('app_reset_password', [
                'email' => (string) $passwordResetRequest->getEmail(),
            ]);
        }

        return $this->render('security/forgot_password.html.twig', [
            'requestForm' => $form,
        ]);
    }

    #[Route('/reinitialiser-mon-mot-de-passe', name: 'app_reset_password')]
    public function resetPassword(Request $request, PasswordResetService $passwordResetService): Response
    {
        $passwordResetVerification = (new PasswordResetVerification())
            ->setEmail($request->query->getString('email'));

        $form = $this->createForm(PasswordResetVerificationType::class, $passwordResetVerification);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $status = $passwordResetService->verifyCodeAndResetPassword(
                (string) $passwordResetVerification->getEmail(),
                (string) $passwordResetVerification->getCode(),
                (string) $passwordResetVerification->getNewPassword(),
            );

            if ($status === 'success') {
                $this->addFlash('success', 'Ton mot de passe a bien été mis à jour. Tu peux maintenant te connecter.');

                return $this->redirectToRoute('app_login');
            }

            $message = match ($status) {
                'expired' => 'Le code a expiré. Tu peux demander un nouveau code.',
                'locked' => 'Trop de tentatives invalides. Demande un nouveau code.',
                default => 'Le code ne correspond pas. Vérifie les 6 chiffres reçus par email.',
            };

            $this->addFlash('error', $message);
        }

        return $this->render('security/reset_password.html.twig', [
            'resetForm' => $form,
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
