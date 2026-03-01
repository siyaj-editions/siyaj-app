<?php

namespace App\Service;

use App\Enum\BookFormat;
use App\Repository\AuthorRepository;
use App\Repository\BookRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Request;

class CatalogService
{
    public const ALLOWED_PER_PAGE = [6, 12, 24, 48];
    public const DEFAULT_PER_PAGE = 12;

    public function __construct(
        private BookRepository $bookRepository,
        private AuthorRepository $authorRepository
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function buildCatalogViewData(Request $request): array
    {
        $filters = $this->normalizeFilters($request);
        $search = $filters['search'];
        $authorId = $filters['authorId'];
        $formatFilter = $filters['formatFilter'];
        $page = $filters['page'];
        $perPage = $filters['perPage'];
        $format = $filters['format'];

        $queryBuilder = $this->bookRepository->createActiveBooksQueryBuilder($search, $authorId, $format);

        $query = $queryBuilder
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
            ->getQuery();

        $paginator = new Paginator($query);
        $totalBooks = count($paginator);
        $totalPages = (int) ceil($totalBooks / $perPage);

        return [
            'books' => $paginator,
            'authors' => $this->authorRepository->findActiveAuthors(),
            'currentSearch' => $search,
            'currentAuthor' => $authorId,
            'currentFormat' => $formatFilter,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalBooks' => $totalBooks,
            'perPage' => $perPage,
            'allowedPerPage' => self::ALLOWED_PER_PAGE,
        ];
    }

    /**
     * @return array{
     *   search: ?string,
     *   authorId: ?int,
     *   formatFilter: ?string,
     *   page: int,
     *   perPage: int,
     *   format: ?BookFormat
     * }
     */
    public function normalizeFilters(Request $request): array
    {
        $search = $request->query->get('search') ?: null;
        $authorId = $request->query->get('author') ? (int) $request->query->get('author') : null;
        $formatFilter = $request->query->get('format') ?: null;
        $page = max(1, $request->query->getInt('page', 1));
        $perPage = $request->query->getInt('perPage', self::DEFAULT_PER_PAGE);

        if (!in_array($perPage, self::ALLOWED_PER_PAGE, true)) {
            $perPage = self::DEFAULT_PER_PAGE;
        }

        $format = null;
        if ($formatFilter && in_array($formatFilter, ['physical', 'digital'], true)) {
            $format = BookFormat::from($formatFilter);
        }

        return [
            'search' => $search,
            'authorId' => $authorId,
            'formatFilter' => $formatFilter,
            'page' => $page,
            'perPage' => $perPage,
            'format' => $format,
        ];
    }
}
