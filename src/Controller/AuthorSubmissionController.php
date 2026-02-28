<?php

namespace App\Controller;

use App\Entity\ManuscriptSubmission;
use App\Form\ManuscriptSubmissionType;
use App\Service\AuthorSubmissionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/auteurs')]
class AuthorSubmissionController extends AbstractController
{
    #[Route('/soumettre-manuscrit', name: 'app_author_manuscript_submit', methods: ['GET', 'POST'])]
    public function submit(Request $request, AuthorSubmissionService $authorSubmissionService): Response
    {
        $submission = new ManuscriptSubmission();
        $form = $this->createForm(ManuscriptSubmissionType::class, $submission);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $authorSubmissionService->submit($submission);

            $this->addFlash('success', 'Votre proposition a bien été envoyée. Notre comité éditorial vous répondra rapidement.');

            return $this->redirectToRoute('app_author_manuscript_submit');
        }

        return $this->render('manuscript/new.html.twig', [
            'submissionForm' => $form,
        ]);
    }
}
