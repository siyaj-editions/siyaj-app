<?php

namespace App\Controller\Admin;

use App\Service\AdminNewsletterService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/newsletter')]
#[IsGranted('ROLE_ADMIN')]
class NewsletterController extends AbstractController
{
    #[Route('', name: 'app_admin_newsletter_index')]
    public function index(AdminNewsletterService $adminNewsletterService): Response
    {
        return $this->render('admin/newsletter/index.html.twig', [
            'subscriptions' => $adminNewsletterService->listLatestSubscriptions(),
        ]);
    }
}
