<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpFoundation\RequestStack;
use App\Service\TmdbApiService;

class RecentlyViewedMoviesListener
{
    private const MAX_RECENT_MOVIES = 5;

    public function __construct(
        private RequestStack $requestStack,
        private TmdbApiService $tmdbApiService
    ) {}

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        
        // Si on consulte les détails d'un film
        if ($request->attributes->get('_route') === 'movie_details') {
            $movieId = $request->attributes->get('id');
            $session = $this->requestStack->getSession();
            
            // Récupérer les films récents
            $recentMovies = $session->get('recent_movies', []);
            
            // Retirer le film s'il existe déjà (pour le mettre en premier)
            $recentMovies = array_filter($recentMovies, fn($id) => $id != $movieId);
            
            // Ajouter le film en début de liste
            array_unshift($recentMovies, $movieId);
            
            // Limiter à MAX_RECENT_MOVIES films
            $recentMovies = array_slice($recentMovies, 0, self::MAX_RECENT_MOVIES);
            
            // Sauvegarder dans la session
            $session->set('recent_movies', $recentMovies);
            
            // Charger et sauvegarder les détails basiques du film pour affichage rapide
            $movieData = $this->tmdbApiService->getMovieDetails($movieId);
            if (!isset($movieData['error'])) {
                $session->set('movie_' . $movieId, [
                    'id' => $movieData['id'],
                    'title' => $movieData['title'],
                    'poster_path' => $movieData['poster_path'] ?? null,
                    'release_date' => $movieData['release_date'] ?? null,
                ]);
            }
        }
    }
}