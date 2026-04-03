<?php

namespace App\Controller\Admin;

use App\Entity\Genre;
use App\Form\GenreFormType;
use App\Service\AdminGenreService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/genres')]
#[IsGranted('ROLE_ADMIN')]
class GenreController extends AbstractController
{
    #[Route('', name: 'app_admin_genre_index')]
    public function index(AdminGenreService $adminGenreService): Response
    {
        return $this->render('admin/genre/index.html.twig', [
            'genres' => $adminGenreService->listGenres(),
        ]);
    }

    #[Route('/nouveau', name: 'app_admin_genre_new')]
    public function new(Request $request, AdminGenreService $adminGenreService): Response
    {
        $genre = new Genre();
        $form = $this->createForm(GenreFormType::class, $genre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $adminGenreService->createGenre($genre);
            $this->addFlash('success', 'Le genre a été créé avec succès.');

            return $this->redirectToRoute('app_admin_genre_index');
        }

        return $this->render('admin/genre/new.html.twig', [
            'genreForm' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_genre_edit')]
    public function edit(Request $request, Genre $genre, AdminGenreService $adminGenreService): Response
    {
        $form = $this->createForm(GenreFormType::class, $genre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $adminGenreService->updateGenre();
            $this->addFlash('success', 'Le genre a été modifié avec succès.');

            return $this->redirectToRoute('app_admin_genre_index');
        }

        return $this->render('admin/genre/edit.html.twig', [
            'genre' => $genre,
            'genreForm' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'app_admin_genre_delete', methods: ['POST'])]
    public function delete(Request $request, Genre $genre, AdminGenreService $adminGenreService): Response
    {
        if ($this->isCsrfTokenValid('delete'.$genre->getId(), $request->request->get('_token'))) {
            $adminGenreService->deleteGenre($genre);
            $this->addFlash('success', 'Le genre a été supprimé avec succès.');
        }

        return $this->redirectToRoute('app_admin_genre_index');
    }
}
