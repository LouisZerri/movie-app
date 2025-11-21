<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpFoundation\RequestStack;
use App\Service\TmdbApiService;

/**
 * Enregistre les derniers films consultés dans la session utilisateur.
 */
class RecentlyViewedMoviesListener
{
    private const MAX_RECENT_MOVIES = 5;

    public function __construct(
        private RequestStack $requestStack,
        private TmdbApiService $tmdbApiService
    ) {}

    /**
     * Ajoute l'ID du film consulté à la liste des films récents, 
     * et stocke ses infos basiques pour affichage rapide (sidebar).
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        // Ne traite que la requête principale
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        
        // Traitement uniquement sur la page de détail d'un film
        if ($request->attributes->get('_route') === 'movie_details') {
            $movieId = $request->attributes->get('id');
            $session = $this->requestStack->getSession();
            
            // Liste actuelle des derniers films vus
            $recentMovies = $session->get('recent_movies', []);
            // On retire le film s'il était déjà dans la liste
            $recentMovies = array_filter($recentMovies, fn($id) => $id != $movieId);
            // On place ce film en tête de liste
            array_unshift($recentMovies, $movieId);
            // On limite à 5 films max
            $recentMovies = array_slice($recentMovies, 0, self::MAX_RECENT_MOVIES);
            $session->set('recent_movies', $recentMovies);
            
            // Sauvegarde dans la session des informations minimales du film
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