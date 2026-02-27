<?php

namespace App\Controller;

use App\Repository\BookRepository;
use App\Service\CatalogService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CatalogController extends AbstractController
{
    #[Route('/catalogue', name: 'app_catalog')]
    public function index(Request $request, CatalogService $catalogService): Response
    {
        return $this->render('catalog/index.html.twig', $catalogService->buildCatalogViewData($request));
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
