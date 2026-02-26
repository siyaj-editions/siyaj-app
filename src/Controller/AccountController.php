<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileFormType;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
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
    public function index(OrderRepository $orderRepository): Response
    {
        $user = $this->getUser();
        $recentOrders = $orderRepository->findBy(
            ['user' => $user],
            ['createdAt' => 'DESC'],
            5
        );

        return $this->render('account/index.html.twig', [
            'user' => $user,
            'recentOrders' => $recentOrders,
        ]);
    }

    #[Route('/profil', name: 'app_account_profile')]
    public function profile(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        $form = $this->createForm(ProfileFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();

            if ($plainPassword) {
                $user->setPassword(
                    $passwordHasher->hashPassword($user, $plainPassword)
                );
            }

            $entityManager->flush();

            $this->addFlash('success', 'Votre profil a été mis à jour avec succès.');

            return $this->redirectToRoute('app_account_profile');
        }

        return $this->render('account/profile.html.twig', [
            'profileForm' => $form,
        ]);
    }

    #[Route('/commandes', name: 'app_account_orders')]
    public function orders(OrderRepository $orderRepository): Response
    {
        $user = $this->getUser();
        $orders = $orderRepository->findByUser($user);

        return $this->render('account/orders.html.twig', [
            'orders' => $orders,
        ]);
    }

    #[Route('/commandes/{id}', name: 'app_account_order_show')]
    public function orderShow(int $id, OrderRepository $orderRepository): Response
    {
        $order = $orderRepository->find($id);

        if (!$order || $order->getUser() !== $this->getUser()) {
            throw $this->createNotFoundException('Commande non trouvée.');
        }

        return $this->render('account/order_show.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('/commandes/{id}/facture', name: 'app_account_order_invoice')]
    public function invoice(int $id, OrderRepository $orderRepository): Response
    {
        $order = $orderRepository->find($id);

        if (!$order || $order->getUser() !== $this->getUser()) {
            throw $this->createNotFoundException('Commande non trouvée.');
        }

        return $this->render('account/invoice.html.twig', [
            'order' => $order,
        ]);
    }
}
