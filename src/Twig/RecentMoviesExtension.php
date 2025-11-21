<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Extension Twig pour l'accès aux derniers films consultés (sidebar).
 */
class RecentMoviesExtension extends AbstractExtension
{
    /**
     * @param RequestStack $requestStack Accès à la session utilisateur
     */
    public function __construct(
        private RequestStack $requestStack
    ) {}

    /**
     * Déclare la fonction Twig 'get_recent_movies'.
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_recent_movies', [$this, 'getRecentMovies']),
        ];
    }

    /**
     * Retourne les infos des 5 derniers films consultés (stockées en session).
     *
     * @return array
     */
    public function getRecentMovies(): array
    {
        $session = $this->requestStack->getSession();
        $recentMovieIds = $session->get('recent_movies', []);
        
        $movies = [];
        foreach ($recentMovieIds as $movieId) {
            // Données du film, sauvegardées par le listener en session
            $movieData = $session->get('movie_' . $movieId);
            if ($movieData) {
                $movies[] = $movieData;
            }
        }
        
        return $movies;
    }
}