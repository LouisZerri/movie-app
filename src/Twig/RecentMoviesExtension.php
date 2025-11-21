<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Symfony\Component\HttpFoundation\RequestStack;

class RecentMoviesExtension extends AbstractExtension
{
    public function __construct(
        private RequestStack $requestStack
    ) {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_recent_movies', [$this, 'getRecentMovies']),
        ];
    }

    public function getRecentMovies(): array
    {
        $session = $this->requestStack->getSession();
        $recentMovieIds = $session->get('recent_movies', []);
        
        $movies = [];
        foreach ($recentMovieIds as $movieId) {
            // Récupérer les données depuis la session (déjà chargées par le Listener)
            $movieData = $session->get('movie_' . $movieId);
            if ($movieData) {
                $movies[] = $movieData;
            }
        }
        
        return $movies;
    }
}