<?php

namespace App\Controller\Admin;

use App\Entity\ManuscriptSubmission;
use App\Repository\ManuscriptSubmissionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Request;

#[Route('/admin/manuscrits')]
#[IsGranted('ROLE_ADMIN')]
class ManuscriptSubmissionController extends AbstractController
{
    #[Route('', name: 'app_admin_manuscript_index')]
    public function index(ManuscriptSubmissionRepository $manuscriptSubmissionRepository): Response
    {
        return $this->render('admin/manuscript_submission/index.html.twig', [
            'submissions' => $manuscriptSubmissionRepository->findLatest(300),
        ]);
    }

    #[Route('/{id}/mark-read', name: 'app_admin_manuscript_mark_read', methods: ['POST'])]
    public function markRead(
        ManuscriptSubmission $manuscriptSubmission,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        if (!$this->isCsrfTokenValid('mark_manuscript_read_' . $manuscriptSubmission->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Token invalide.');

            return $this->redirectToRoute('app_admin_manuscript_index');
        }

        $manuscriptSubmission->setIsReadByAdmin(true);
        $entityManager->flush();
        $this->addFlash('success', 'Demande marquée comme lue.');

        return $this->redirectToRoute('app_admin_manuscript_index');
    }

    #[Route('/{id}/mark-unread', name: 'app_admin_manuscript_mark_unread', methods: ['POST'])]
    public function markUnread(
        ManuscriptSubmission $manuscriptSubmission,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        if (!$this->isCsrfTokenValid('mark_manuscript_unread_' . $manuscriptSubmission->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Token invalide.');

            return $this->redirectToRoute('app_admin_manuscript_index');
        }

        $manuscriptSubmission->setIsReadByAdmin(false);
        $entityManager->flush();
        $this->addFlash('success', 'Demande marquée comme non lue.');

        return $this->redirectToRoute('app_admin_manuscript_index');
    }
}
