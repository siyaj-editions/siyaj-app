<?php

namespace App\Controller\Admin;

use App\Entity\Author;
use App\Form\AuthorFormType;
use App\Service\AdminAuthorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/auteurs')]
#[IsGranted('ROLE_ADMIN')]
class AuthorController extends AbstractController
{
    #[Route('', name: 'app_admin_author_index')]
    public function index(AdminAuthorService $adminAuthorService): Response
    {
        return $this->render('admin/author/index.html.twig', [
            'authors' => $adminAuthorService->listAuthors(),
        ]);
    }

    #[Route('/nouveau', name: 'app_admin_author_new')]
    public function new(Request $request, AdminAuthorService $adminAuthorService): Response
    {
        $author = new Author();
        $form = $this->createForm(AuthorFormType::class, $author);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $adminAuthorService->createAuthor($author);

            $this->addFlash('success', 'L\'auteur a été créé avec succès.');

            return $this->redirectToRoute('app_admin_author_index');
        }

        return $this->render('admin/author/new.html.twig', [
            'authorForm' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_author_edit')]
    public function edit(Request $request, Author $author, AdminAuthorService $adminAuthorService): Response
    {
        $form = $this->createForm(AuthorFormType::class, $author);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $adminAuthorService->updateAuthor();

            $this->addFlash('success', 'L\'auteur a été modifié avec succès.');

            return $this->redirectToRoute('app_admin_author_index');
        }

        return $this->render('admin/author/edit.html.twig', [
            'author' => $author,
            'authorForm' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_admin_author_delete', methods: ['POST'])]
    public function delete(Request $request, Author $author, AdminAuthorService $adminAuthorService): Response
    {
        if ($this->isCsrfTokenValid('delete'.$author->getId(), $request->request->get('_token'))) {
            $adminAuthorService->deleteAuthor($author);

            $this->addFlash('success', 'L\'auteur a été supprimé avec succès.');
        }

        return $this->redirectToRoute('app_admin_author_index');
    }
}
