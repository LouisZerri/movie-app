<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class TmdbApiService
{
    private const API_BASE_URL = 'https://api.themoviedb.org/3';
    private const IMAGE_BASE_URL = 'https://image.tmdb.org/t/p/w500';
    private const CACHE_TTL = 3600; // 1 heure
    
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $tmdbApiKey,
        private CacheInterface $cache
    ) {}

    /**
     * Rechercher des films par titre
     */
    public function searchMovies(string $query, int $page = 1): array
    {
        $cacheKey = 'tmdb_search_' . md5($query . '_' . $page);
        
        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($query, $page) {
            $item->expiresAfter(self::CACHE_TTL);
            
            try {
                $response = $this->httpClient->request('GET', self::API_BASE_URL . '/search/movie', [
                    'query' => [
                        'api_key' => $this->tmdbApiKey,
                        'query' => $query,
                        'page' => $page,
                        'language' => 'fr-FR',
                    ],
                ]);

                return $response->toArray();
            } catch (TransportExceptionInterface $e) {
                return ['error' => 'API request failed: ' . $e->getMessage()];
            }
        });
    }

    /**
     * Obtenir les détails d'un film par ID
     */
    public function getMovieDetails(int $movieId): array
    {
        $cacheKey = 'tmdb_movie_' . $movieId;
        
        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($movieId) {
            $item->expiresAfter(self::CACHE_TTL);
            
            try {
                $response = $this->httpClient->request('GET', self::API_BASE_URL . "/movie/{$movieId}", [
                    'query' => [
                        'api_key' => $this->tmdbApiKey,
                        'language' => 'fr-FR',
                        'append_to_response' => 'credits,videos',
                    ],
                ]);

                return $response->toArray();
            } catch (TransportExceptionInterface $e) {
                return ['error' => 'API request failed: ' . $e->getMessage()];
            }
        });
    }

    /**
     * Obtenir les films populaires
     */
    public function getPopularMovies(int $page = 1): array
    {
        $cacheKey = 'tmdb_popular_' . $page;
        
        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($page) {
            $item->expiresAfter(self::CACHE_TTL);
            
            try {
                $response = $this->httpClient->request('GET', self::API_BASE_URL . '/movie/popular', [
                    'query' => [
                        'api_key' => $this->tmdbApiKey,
                        'page' => $page,
                        'language' => 'fr-FR',
                    ],
                ]);

                return $response->toArray();
            } catch (TransportExceptionInterface $e) {
                return ['error' => 'API request failed: ' . $e->getMessage()];
            }
        });
    }

    /**
     * Obtenir les films à venir
     */
    public function getUpcomingMovies(int $page = 1): array
    {
        $cacheKey = 'tmdb_upcoming_' . $page;
        
        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($page) {
            $item->expiresAfter(self::CACHE_TTL);
            
            try {
                $response = $this->httpClient->request('GET', self::API_BASE_URL . '/movie/upcoming', [
                    'query' => [
                        'api_key' => $this->tmdbApiKey,
                        'page' => $page,
                        'language' => 'fr-FR',
                    ],
                ]);

                return $response->toArray();
            } catch (TransportExceptionInterface $e) {
                return ['error' => 'API request failed: ' . $e->getMessage()];
            }
        });
    }

    /**
     * Obtenir les films par genre
     */
    public function getMoviesByGenre(int $genreId, int $page = 1): array
    {
        $cacheKey = 'tmdb_genre_' . $genreId . '_' . $page;
        
        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($genreId, $page) {
            $item->expiresAfter(self::CACHE_TTL);
            
            try {
                $response = $this->httpClient->request('GET', self::API_BASE_URL . '/discover/movie', [
                    'query' => [
                        'api_key' => $this->tmdbApiKey,
                        'with_genres' => $genreId,
                        'page' => $page,
                        'language' => 'fr-FR',
                    ],
                ]);

                return $response->toArray();
            } catch (TransportExceptionInterface $e) {
                return ['error' => 'API request failed: ' . $e->getMessage()];
            }
        });
    }

    /**
     * Obtenir les genres de films
     */
    public function getGenres(): array
    {
        $cacheKey = 'tmdb_genres';
        
        return $this->cache->get($cacheKey, function (ItemInterface $item) {
            $item->expiresAfter(86400); // 24h
            
            try {
                $response = $this->httpClient->request('GET', self::API_BASE_URL . '/genre/movie/list', [
                    'query' => [
                        'api_key' => $this->tmdbApiKey,
                        'language' => 'fr-FR',
                    ],
                ]);

                return $response->toArray();
            } catch (TransportExceptionInterface $e) {
                return ['error' => 'API request failed: ' . $e->getMessage()];
            }
        });
    }

    /**
     * Obtenir l'URL complète de l'image
     */
    public function getImageUrl(?string $path): ?string
    {
        return $path ? self::IMAGE_BASE_URL . $path : null;
    }
}