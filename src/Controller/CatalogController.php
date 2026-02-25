<?php

namespace App\Controller;

use App\Enum\BookFormat;
use App\Repository\AuthorRepository;
use App\Repository\BookRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CatalogController extends AbstractController
{
    #[Route('/catalogue', name: 'app_catalog')]
    public function index(
        Request $request,
        BookRepository $bookRepository,
        AuthorRepository $authorRepository
    ): Response {
        $search = $request->query->get('search');
        $authorId = $request->query->get('author');
        $formatFilter = $request->query->get('format');

        $format = null;
        if ($formatFilter && in_array($formatFilter, ['physical', 'digital'])) {
            $format = BookFormat::from($formatFilter);
        }

        $books = $bookRepository->findActiveBooksWithFilters($search, $authorId, $format);
        $authors = $authorRepository->findActiveAuthors();

        return $this->render('catalog/index.html.twig', [
            'books' => $books,
            'authors' => $authors,
            'currentSearch' => $search,
            'currentAuthor' => $authorId,
            'currentFormat' => $formatFilter,
        ]);
    }

    #[Route('/livres/{slug}', name: 'app_book_show')]
    public function show(string $slug, BookRepository $bookRepository): Response
    {
        $book = $bookRepository->findBySlug($slug);

        if (!$book) {
            throw $this->createNotFoundException('Le livre demandé n\'existe pas.');
        }

        return $this->render('catalog/show.html.twig', [
            'book' => $book,
        ]);
    }
}
