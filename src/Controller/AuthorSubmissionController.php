<?php

namespace App\Controller;

use App\Entity\ManuscriptSubmission;
use App\Form\ManuscriptSubmissionType;
use App\Service\AuthorSubmissionService;
use App\Service\HoneypotService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/auteurs')]
class AuthorSubmissionController extends AbstractController
{
    #[Route('/soumettre-manuscrit', name: 'app_author_manuscript_submit', methods: ['GET', 'POST'])]
    public function submit(Request $request, AuthorSubmissionService $authorSubmissionService, HoneypotService $honeypotService): Response
    {
        $submission = new ManuscriptSubmission();
        $form = $this->createForm(ManuscriptSubmissionType::class, $submission);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($honeypotService->isTriggered($form)) {
                $this->addFlash('success', 'Votre proposition a bien été envoyée. Notre comité éditorial vous répondra rapidement.');

                return $this->redirectToRoute('app_author_manuscript_submit');
            }

            /** @var UploadedFile|null $manuscriptFile */
            $manuscriptFile = $form->get('manuscriptFile')->getData();
            $authorSubmissionService->submit($submission, $manuscriptFile);

            $this->addFlash('success', 'Votre proposition a bien été envoyée. Notre comité éditorial vous répondra rapidement.');

            return $this->redirectToRoute('app_author_manuscript_submit');
        }

        return $this->render('manuscript/new.html.twig', [
            'submissionForm' => $form,
        ]);
    }
}
