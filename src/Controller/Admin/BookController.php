<?php

namespace App\Controller\Admin;

use App\Entity\Book;
use App\Form\BookFormType;
use App\Service\AdminBookService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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
        $form = $this->createForm(BookFormType::class, $book, [
            'genre_choices' => $adminBookService->listGenres(),
            'author_choices' => $adminBookService->listAuthorNames(),
        ]);
        $this->seedCollectionField($form, 'authorNames', $book->getAuthors()->map(static fn ($author) => $author->getName())->toArray());
        $this->seedCollectionField($form, 'genreNames', $book->getGenres()->map(static fn ($genre) => $genre->getName())->toArray());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile|null $coverImageFile */
            $coverImageFile = $form->get('coverImageFile')->getData();
            $adminBookService->createBook(
                $book,
                (array) $form->get('authorNames')->getData(),
                (array) $form->get('genreNames')->getData(),
                $coverImageFile
            );

            $this->addFlash('success', 'Le livre a été créé avec succès.');

            return $this->redirectToRoute('app_admin_book_index');
        }

        return $this->render('admin/book/new.html.twig', [
            'bookForm' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_book_edit')]
    public function edit(Request $request, Book $book, AdminBookService $adminBookService): Response
    {
        $form = $this->createForm(BookFormType::class, $book, [
            'genre_choices' => $adminBookService->listGenres(),
            'author_choices' => $adminBookService->listAuthorNames(),
        ]);
        $this->seedCollectionField($form, 'authorNames', $book->getAuthors()->map(static fn ($author) => $author->getName())->toArray());
        $this->seedCollectionField($form, 'genreNames', $book->getGenres()->map(static fn ($genre) => $genre->getName())->toArray());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile|null $coverImageFile */
            $coverImageFile = $form->get('coverImageFile')->getData();
            $adminBookService->updateBook(
                $book,
                (array) $form->get('authorNames')->getData(),
                (array) $form->get('genreNames')->getData(),
                $coverImageFile
            );

            $this->addFlash('success', 'Le livre a été modifié avec succès.');

            return $this->redirectToRoute('app_admin_book_index');
        }

        return $this->render('admin/book/edit.html.twig', [
            'book' => $book,
            'bookForm' => $form->createView(),
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

    private function seedCollectionField(\Symfony\Component\Form\FormInterface $form, string $fieldName, array $values): void
    {
        if (!$form->has($fieldName)) {
            return;
        }

        $filteredValues = array_values(array_filter($values, static fn ($value) => is_string($value) && trim($value) !== ''));
        $form->get($fieldName)->setData($filteredValues !== [] ? $filteredValues : ['']);
    }
}
