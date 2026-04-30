<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\AddressFormType;
use App\Form\ProfileFormType;
use App\Service\AccountService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/mon-compte')]
#[IsGranted('ROLE_USER')]
class AccountController extends AbstractController
{
    #[Route('', name: 'app_account')]
    public function index(AccountService $accountService): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('account/index.html.twig', [
            'user' => $user,
            'recentOrders' => $accountService->getRecentOrders($user),
        ]);
    }

    #[Route('/adresse', name: 'app_account_address')]
    public function address(
        Request $request,
        AccountService $accountService
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        $addresses = $accountService->getUserAddresses($user);
        $address = $accountService->createDefaultAddressForUser($user);

        $addressForm = $this->createForm(AddressFormType::class, $address);
        $addressForm->handleRequest($request);

        if ($addressForm->isSubmitted() && $addressForm->isValid()) {
            $address->setUser($user);
            $accountService->saveAddress($address);

            $this->addFlash('success', 'Adresse enregistrée. Elle sera préremplie au checkout.');

            return $this->redirectToRoute('app_account_address');
        }

        return $this->render('account/address.html.twig', [
            'addresses' => $addresses,
            'addressForm' => $addressForm,
        ]);
    }

    #[Route('/adresse/{id}/supprimer', name: 'app_account_address_delete', methods: ['POST'])]
    public function deleteAddress(
        int $id,
        Request $request,
        AccountService $accountService
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        $address = $accountService->findUserAddressById($user, $id);

        if (!$address) {
            throw $this->createNotFoundException('Adresse non trouvée.');
        }

        if (!$this->isCsrfTokenValid('delete_address_' . $address->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Jeton CSRF invalide.');

            return $this->redirectToRoute('app_account_address');
        }

        $accountService->deleteAddress($address);

        $this->addFlash('success', 'Adresse supprimée.');

        return $this->redirectToRoute('app_account_address');
    }

    #[Route('/adresse/{id}/defaut', name: 'app_account_address_default', methods: ['POST'])]
    public function setDefaultAddress(
        int $id,
        Request $request,
        AccountService $accountService
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        $address = $accountService->findUserAddressById($user, $id);

        if (!$address) {
            throw $this->createNotFoundException('Adresse non trouvée.');
        }

        if (!$this->isCsrfTokenValid('default_address_' . $address->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Jeton CSRF invalide.');

            return $this->redirectToRoute('app_account_address');
        }

        $accountService->setDefaultAddress($user, $address);
        $this->addFlash('success', 'Adresse par défaut mise à jour.');

        return $this->redirectToRoute('app_account_address');
    }

    #[Route('/adresse/{id}/modifier', name: 'app_account_address_edit', methods: ['GET', 'POST'])]
    public function editAddress(
        int $id,
        Request $request,
        AccountService $accountService
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        $address = $accountService->findUserAddressById($user, $id);

        if (!$address) {
            throw $this->createNotFoundException('Adresse non trouvée.');
        }

        $form = $this->createForm(AddressFormType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $address->setUser($user);
            $accountService->saveAddress($address);

            $this->addFlash('success', 'Adresse mise à jour.');

            return $this->redirectToRoute('app_account_address');
        }

        return $this->render('account/address_edit.html.twig', [
            'address' => $address,
            'addressForm' => $form,
        ]);
    }

    #[Route('/profil', name: 'app_account_profile')]
    public function profile(
        Request $request,
        AccountService $accountService,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        $form = $this->createForm(ProfileFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            $accountService->updateProfile($user, is_string($plainPassword) ? $plainPassword : null, $passwordHasher);

            $this->addFlash('success', 'Votre profil a été mis à jour avec succès.');

            return $this->redirectToRoute('app_account_profile');
        }

        return $this->render('account/profile.html.twig', [
            'profileForm' => $form,
        ]);
    }

    #[Route('/commandes', name: 'app_account_orders')]
    public function orders(AccountService $accountService): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('account/orders.html.twig', [
            'orders' => $accountService->getUserOrders($user),
        ]);
    }

    #[Route('/commandes/{id}', name: 'app_account_order_show')]
    public function orderShow(int $id, AccountService $accountService): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $order = $accountService->findUserOrderById($user, $id);

        if (!$order) {
            throw $this->createNotFoundException('Commande non trouvée.');
        }

        return $this->render('account/order_show.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('/commandes/{id}/reception', name: 'app_account_order_mark_received', methods: ['POST'])]
    public function markOrderAsReceived(int $id, Request $request, AccountService $accountService): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $order = $accountService->findUserOrderById($user, $id);

        if (!$order) {
            throw $this->createNotFoundException('Commande non trouvée.');
        }

        if (!$this->isCsrfTokenValid('mark_order_received_' . $order->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Jeton CSRF invalide.');

            return $this->redirectToRoute('app_account_order_show', ['id' => $order->getId()]);
        }

        if ($accountService->markOrderAsReceived($order)) {
            $this->addFlash('success', 'Merci. La commande a été marquée comme reçue.');
        } else {
            $this->addFlash('warning', 'Cette commande ne peut pas encore être marquée comme reçue.');
        }

        return $this->redirectToRoute('app_account_order_show', ['id' => $order->getId()]);
    }

    #[Route('/commandes/{id}/facture', name: 'app_account_order_invoice')]
    public function invoice(int $id, AccountService $accountService): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $order = $accountService->findUserOrderById($user, $id);

        if (!$order) {
            throw $this->createNotFoundException('Commande non trouvée.');
        }

        return $this->render('account/invoice.html.twig', [
            'order' => $order,
        ]);
    }
}
