<?php

namespace App\Controller\Admin;

use App\Entity\Book;
use App\Form\BookFormType;
use App\Service\AdminBookService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/livres')]
#[IsGranted('ROLE_ADMIN')]
class BookController extends AbstractController
{
    #[Route('', name: 'app_admin_book_index')]
    public function index(AdminBookService $adminBookService): Response
    {
        return $this->render('admin/book/index.html.twig', [
            'books' => $adminBookService->listBooks(),
        ]);
    }

    #[Route('/nouveau', name: 'app_admin_book_new')]
    public function new(Request $request, AdminBookService $adminBookService): Response
    {
        $book = new Book();
        $form = $this->createForm(BookFormType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $adminBookService->createBook($book);

            $this->addFlash('success', 'Le livre a été créé avec succès.');

            return $this->redirectToRoute('app_admin_book_index');
        }

        return $this->render('admin/book/new.html.twig', [
            'bookForm' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_book_edit')]
    public function edit(Request $request, Book $book, AdminBookService $adminBookService): Response
    {
        $form = $this->createForm(BookFormType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $adminBookService->updateBook();

            $this->addFlash('success', 'Le livre a été modifié avec succès.');

            return $this->redirectToRoute('app_admin_book_index');
        }

        return $this->render('admin/book/edit.html.twig', [
            'book' => $book,
            'bookForm' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_admin_book_delete', methods: ['POST'])]
    public function delete(Request $request, Book $book, AdminBookService $adminBookService): Response
    {
        if ($this->isCsrfTokenValid('delete'.$book->getId(), $request->request->get('_token'))) {
            $adminBookService->deleteBook($book);

            $this->addFlash('success', 'Le livre a été supprimé avec succès.');
        }

        return $this->redirectToRoute('app_admin_book_index');
    }
}
