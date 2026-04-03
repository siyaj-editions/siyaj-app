<?php

namespace App\Controller;

use App\Form\ContactFormType;
use App\Model\ContactMessage;
use App\Service\ContactService;
use App\Service\HoneypotService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact', methods: ['GET', 'POST'])]
    public function index(Request $request, ContactService $contactService, HoneypotService $honeypotService): Response
    {
        $contactMessage = new ContactMessage();
        $form = $this->createForm(ContactFormType::class, $contactMessage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($honeypotService->isTriggered($form)) {
                $this->addFlash('success', 'Votre message a bien été envoyé. Nous vous répondrons rapidement.');

                return $this->redirectToRoute('app_contact');
            }

            $contactService->send($contactMessage);

            $this->addFlash('success', 'Votre message a bien été envoyé. Nous vous répondrons rapidement.');

            return $this->redirectToRoute('app_contact');
        }

        return $this->render('contact/index.html.twig', [
            'contactForm' => $form->createView(),
        ]);
    }
}
