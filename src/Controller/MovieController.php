<?php

namespace App\Controller;

use App\Service\TmdbApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur pour la gestion des films.
 */
#[Route('/movies')]
class MovieController extends AbstractController
{
    /**
     * Service pour interagir avec l'API TMDB.
     */
    public function __construct(
        private TmdbApiService $tmdbApiService
    ) {}

    /**
     * Affiche la liste des films populaires (page d'accueil films).
     *
     * @param Request $request
     * @return Response
     */
    #[Route('/', name: 'movie_index')]
    public function index(Request $request): Response
    {
        $page = max(1, $request->query->getInt('page', 1));

        $apiResponse = $this->tmdbApiService->getPopularMovies($page);

        return $this->render('movie/index.html.twig', [
            'movies' => $apiResponse['results'] ?? [],
            'page' => $page,
            'totalPages' => $apiResponse['total_pages'] ?? 1,
            'hasError' => isset($apiResponse['error']),
            'errorMessage' => $apiResponse['error'] ?? null,
        ]);
    }

    /**
     * Recherche de films par titre.
     *
     * @param Request $request
     * @return Response
     */
    #[Route('/search', name: 'movie_search')]
    public function search(Request $request): Response
    {
        $query = $request->query->get('q', '');
        $page = max(1, $request->query->getInt('page', 1));
        $movies = [];
        $totalPages = 1;
        $hasError = false;
        $errorMessage = null;

        if (!empty($query)) {
            $apiResponse = $this->tmdbApiService->searchMovies($query, $page);
            $movies = $apiResponse['results'] ?? [];
            $totalPages = $apiResponse['total_pages'] ?? 1;
            $hasError = isset($apiResponse['error']);
            $errorMessage = $apiResponse['error'] ?? null;
        }

        return $this->render('movie/search.html.twig', [
            'movies' => $movies,
            'query' => $query,
            'page' => $page,
            'totalPages' => $totalPages,
            'hasError' => $hasError,
            'errorMessage' => $errorMessage,
            'suggestions' => $request->attributes->get('suggestions', []),
        ]);
    }

    /**
     * Affiche les détails d'un film par son identifiant.
     *
     * @param int $id
     * @return Response
     */
    #[Route('/{id}', name: 'movie_details', requirements: ['id' => '\d+'])]
    public function details(int $id): Response
    {
        $movie = $this->tmdbApiService->getMovieDetails($id);

        if (isset($movie['error'])) {
            $this->addFlash('error', 'Film non trouvé ou erreur API');
            return $this->redirectToRoute('movie_index');
        }

        return $this->render('movie/details.html.twig', [
            'movie' => $movie,
        ]);
    }

    /**
     * Liste les films d'un genre donné.
     *
     * @param int $genreId
     * @param Request $request
     * @return Response
     */
    #[Route('/genre/{genreId}', name: 'movie_by_genre', requirements: ['genreId' => '\d+'])]
    public function byGenre(int $genreId, Request $request): Response
    {
        $page = max(1, $request->query->getInt('page', 1));
        $apiResponse = $this->tmdbApiService->getMoviesByGenre($genreId, $page);

        // Recherche du nom du genre
        $genres = $this->tmdbApiService->getGenres();
        $genreName = 'Films';
        if (isset($genres['genres'])) {
            foreach ($genres['genres'] as $genre) {
                if ($genre['id'] === $genreId) {
                    $genreName = $genre['name'];
                    break;
                }
            }
        }

        return $this->render('movie/genre.html.twig', [
            'movies' => $apiResponse['results'] ?? [],
            'genre' => $genreName,
            'genreId' => $genreId,
            'page' => $page,
            'totalPages' => $apiResponse['total_pages'] ?? 1,
            'hasError' => isset($apiResponse['error']),
            'errorMessage' => $apiResponse['error'] ?? null,
        ]);
    }

    /**
     * Affiche les films à venir.
     *
     * @param Request $request
     * @return Response
     */
    #[Route('/upcoming', name: 'movie_upcoming')]
    public function upcoming(Request $request): Response
    {
        $page = max(1, $request->query->getInt('page', 1));
        $apiResponse = $this->tmdbApiService->getUpcomingMovies($page);

        return $this->render('movie/upcoming.html.twig', [
            'movies' => $apiResponse['results'] ?? [],
            'page' => $page,
            'totalPages' => $apiResponse['total_pages'] ?? 1,
            'hasError' => isset($apiResponse['error']),
            'errorMessage' => $apiResponse['error'] ?? null,
        ]);
    }
}