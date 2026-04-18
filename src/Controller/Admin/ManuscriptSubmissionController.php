<?php

namespace App\Controller\Admin;

use App\Entity\ManuscriptSubmission;
use App\Service\AdminManuscriptService;
use App\Service\ManuscriptStorage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/manuscrits')]
#[IsGranted('ROLE_ADMIN')]
class ManuscriptSubmissionController extends AbstractController
{
    #[Route('', name: 'app_admin_manuscript_index')]
    public function index(AdminManuscriptService $adminManuscriptService): Response
    {
        return $this->render('admin/manuscript_submission/index.html.twig', [
            'submissions' => $adminManuscriptService->listLatestSubmissions(),
        ]);
    }

    #[Route('/{id}/download', name: 'app_admin_manuscript_download', methods: ['GET'])]
    public function download(ManuscriptSubmission $manuscriptSubmission, ManuscriptStorage $manuscriptStorage): Response
    {
        $path = $manuscriptStorage->resolvePath($manuscriptSubmission->getManuscriptPath());

        if (!$path || !is_file($path)) {
            throw $this->createNotFoundException('Le manuscrit demandé est introuvable.');
        }

        $response = new BinaryFileResponse($path);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            sprintf('manuscrit-%s-%s.pdf', $manuscriptSubmission->getId(), date('Ymd'))
        );

        return $response;
    }

    #[Route('/{id}/mark-read', name: 'app_admin_manuscript_mark_read', methods: ['POST'])]
    public function markRead(
        ManuscriptSubmission $manuscriptSubmission,
        Request $request,
        AdminManuscriptService $adminManuscriptService
    ): Response {
        if (!$this->isCsrfTokenValid('mark_manuscript_read_' . $manuscriptSubmission->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Token invalide.');

            return $this->redirectToRoute('app_admin_manuscript_index');
        }

        $adminManuscriptService->markAsRead($manuscriptSubmission);
        $this->addFlash('success', 'Demande marquée comme lue.');

        return $this->redirectToRoute('app_admin_manuscript_index');
    }

    #[Route('/{id}/mark-unread', name: 'app_admin_manuscript_mark_unread', methods: ['POST'])]
    public function markUnread(
        ManuscriptSubmission $manuscriptSubmission,
        Request $request,
        AdminManuscriptService $adminManuscriptService
    ): Response {
        if (!$this->isCsrfTokenValid('mark_manuscript_unread_' . $manuscriptSubmission->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Token invalide.');

            return $this->redirectToRoute('app_admin_manuscript_index');
        }

        $adminManuscriptService->markAsUnread($manuscriptSubmission);
        $this->addFlash('success', 'Demande marquée comme non lue.');

        return $this->redirectToRoute('app_admin_manuscript_index');
    }
}
