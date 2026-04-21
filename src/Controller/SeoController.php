<?php

namespace App\Controller;

use App\Repository\BookRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SeoController extends AbstractController
{
    #[Route('/robots.txt', name: 'app_robots_txt', methods: ['GET'])]
    public function robots(): Response
    {
        $response = $this->render('seo/robots.txt.twig', [
            'sitemapUrl' => $this->generateUrl('app_sitemap', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);
        $response->headers->set('Content-Type', 'text/plain; charset=UTF-8');

        return $response;
    }

    #[Route('/sitemap.xml', name: 'app_sitemap', methods: ['GET'])]
    public function sitemap(BookRepository $bookRepository): Response
    {
        $staticPages = [
            [
                'loc' => $this->generateUrl('app_home', [], UrlGeneratorInterface::ABSOLUTE_URL),
                'lastmod' => null,
                'changefreq' => 'weekly',
                'priority' => '1.0',
            ],
            [
                'loc' => $this->generateUrl('app_catalog', [], UrlGeneratorInterface::ABSOLUTE_URL),
                'lastmod' => null,
                'changefreq' => 'weekly',
                'priority' => '0.9',
            ],
            [
                'loc' => $this->generateUrl('app_about', [], UrlGeneratorInterface::ABSOLUTE_URL),
                'lastmod' => null,
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ],
            [
                'loc' => $this->generateUrl('app_contact', [], UrlGeneratorInterface::ABSOLUTE_URL),
                'lastmod' => null,
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ],
            [
                'loc' => $this->generateUrl('app_author_manuscript_submit', [], UrlGeneratorInterface::ABSOLUTE_URL),
                'lastmod' => null,
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ],
            [
                'loc' => $this->generateUrl('app_legal_mentions', [], UrlGeneratorInterface::ABSOLUTE_URL),
                'lastmod' => null,
                'changefreq' => 'yearly',
                'priority' => '0.3',
            ],
            [
                'loc' => $this->generateUrl('app_privacy', [], UrlGeneratorInterface::ABSOLUTE_URL),
                'lastmod' => null,
                'changefreq' => 'yearly',
                'priority' => '0.3',
            ],
            [
                'loc' => $this->generateUrl('app_cgv', [], UrlGeneratorInterface::ABSOLUTE_URL),
                'lastmod' => null,
                'changefreq' => 'yearly',
                'priority' => '0.3',
            ],
        ];

        $bookPages = array_map(function ($book): array {
            return [
                'loc' => $this->generateUrl('app_book_show', ['slug' => $book->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL),
                'lastmod' => ($book->getPublishedAt() ?? $book->getCreatedAt())?->format('Y-m-d'),
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ];
        }, $bookRepository->findActiveBooks());

        $response = $this->render('seo/sitemap.xml.twig', [
            'urls' => array_merge($staticPages, $bookPages),
        ]);
        $response->headers->set('Content-Type', 'application/xml; charset=UTF-8');

        return $response;
    }
}
