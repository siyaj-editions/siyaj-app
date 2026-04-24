<?php

namespace App\Controller;

use App\Entity\Newsletter;
use App\Form\NewsletterType;
use App\Service\HomeService;
use App\Service\HoneypotService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        Request $request,
        HomeService $homeService,
        HoneypotService $honeypotService
    ): Response
    {
        $latestBooks = $homeService->getLatestActiveBooks();

        $newsletter = new Newsletter();
        $newsletterForm = $this->createForm(NewsletterType::class, $newsletter);
        $newsletterForm->handleRequest($request);

        if ($newsletterForm->isSubmitted() && $newsletterForm->isValid()) {
            if ($honeypotService->isTriggered($newsletterForm)) {
                $this->addFlash('success', 'Inscription à la newsletter confirmée.');

                return $this->redirectToRoute('app_home');
            }

            $result = $homeService->subscribeToNewsletter($newsletter);
            $this->addFlash($result->flashType, $result->flashMessage);

            return $this->redirectToRoute('app_home');
        }

        return $this->render('home/index.html.twig', [
            'latestBooks' => $latestBooks,
            'newsletterForm' => $newsletterForm,
        ]);
    }

    #[Route('/a-propos', name: 'app_about')]
    public function about(): Response
    {
        return $this->render('home/about.html.twig');
    }

    #[Route('/mentions-legales', name: 'app_legal_mentions')]
    public function legalMentions(): Response
    {
        return $this->render('home/legal_mentions.html.twig');
    }

    #[Route('/cgv', name: 'app_cgv')]
    public function cgv(): Response
    {
        return $this->render('home/cgv.html.twig');
    }

    #[Route('/cgu', name: 'app_cgu')]
    public function cgu(): Response
    {
        return $this->render('home/cgu.html.twig');
    }

    #[Route('/confidentialite', name: 'app_privacy')]
    public function privacy(): Response
    {
        return $this->render('home/privacy.html.twig');
    }
}
