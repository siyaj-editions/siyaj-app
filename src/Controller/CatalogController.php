<?php

namespace App\Controller;

use App\Enum\BookFormat;
use App\Repository\AuthorRepository;
use App\Repository\BookRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CatalogController extends AbstractController
{
    private const ALLOWED_PER_PAGE = [6, 12, 24, 48];
    private const DEFAULT_PER_PAGE = 12;

    #[Route('/catalogue', name: 'app_catalog')]
    public function index(
        Request $request,
        BookRepository $bookRepository,
        AuthorRepository $authorRepository
    ): Response {
        $search = $request->query->get('search') ?: null;
        $authorId = $request->query->get('author') ? (int) $request->query->get('author') : null;
        $formatFilter = $request->query->get('format') ?: null;
        $page = max(1, $request->query->getInt('page', 1));
        $perPage = $request->query->getInt('perPage', self::DEFAULT_PER_PAGE);

        // Valider perPage
        if (!in_array($perPage, self::ALLOWED_PER_PAGE)) {
            $perPage = self::DEFAULT_PER_PAGE;
        }

        $format = null;
        if ($formatFilter && in_array($formatFilter, ['physical', 'digital'])) {
            $format = BookFormat::from($formatFilter);
        }

        $queryBuilder = $bookRepository->createActiveBooksQueryBuilder($search, $authorId, $format);

        // Pagination
        $query = $queryBuilder
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
            ->getQuery();

        $paginator = new Paginator($query);
        $totalBooks = count($paginator);
        $totalPages = (int) ceil($totalBooks / $perPage);

        $authors = $authorRepository->findActiveAuthors();

        return $this->render('catalog/index.html.twig', [
            'books' => $paginator,
            'authors' => $authors,
            'currentSearch' => $search,
            'currentAuthor' => $authorId,
            'currentFormat' => $formatFilter,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalBooks' => $totalBooks,
            'perPage' => $perPage,
            'allowedPerPage' => self::ALLOWED_PER_PAGE,
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
