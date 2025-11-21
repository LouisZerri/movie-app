<?php

namespace App\Tests\Unit\Service;

use App\Service\TmdbApiService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Tests unitaires pour le service TmdbApiService (API TMDB et cache).
 */
class TmdbApiServiceTest extends TestCase
{
    /**
     * Simule un cache toujours vide (passe directement au callback).
     */
    private function createCacheMock(): CacheInterface
    {
        $cache = $this->getMockBuilder(CacheInterface::class)->getMock();
        $cache->method('get')->willReturnCallback(function ($key, $callback) {
            $item = $this->createMock(ItemInterface::class);
            return $callback($item);
        });
        
        return $cache;
    }

    /**
     * Vérifie que searchMovies renvoie bien la structure attendue.
     */
    public function testSearchMoviesReturnsResults(): void
    {
        $mockResponse = new MockResponse(json_encode([
            'results' => [
                [
                    'id' => 550,
                    'title' => 'Fight Club',
                    'release_date' => '1999-10-15',
                    'vote_average' => 8.4
                ]
            ],
            'total_pages' => 1
        ]));

        $httpClient = new MockHttpClient($mockResponse);
        $cache = $this->createCacheMock();
        $service = new TmdbApiService($httpClient, 'fake_api_key', $cache);
        $result = $service->searchMovies('Fight Club');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('results', $result);
        $this->assertCount(1, $result['results']);
        $this->assertEquals('Fight Club', $result['results'][0]['title']);
    }

    /**
     * Vérifie la récupération des détails d'un film.
     */
    public function testGetMovieDetailsReturnsMovieData(): void
    {
        $mockResponse = new MockResponse(json_encode([
            'id' => 550,
            'title' => 'Fight Club',
            'overview' => 'A ticking-time-bomb insomniac...',
            'release_date' => '1999-10-15',
            'vote_average' => 8.4,
            'runtime' => 139
        ]));

        $httpClient = new MockHttpClient($mockResponse);
        $cache = $this->createCacheMock();
        $service = new TmdbApiService($httpClient, 'fake_api_key', $cache);
        $result = $service->getMovieDetails(550);

        $this->assertIsArray($result);
        $this->assertEquals(550, $result['id']);
        $this->assertEquals('Fight Club', $result['title']);
        $this->assertEquals(139, $result['runtime']);
    }

    /**
     * Vérifie que getPopularMovies renvoie des films populaires.
     */
    public function testGetPopularMoviesReturnsResults(): void
    {
        $mockResponse = new MockResponse(json_encode([
            'results' => [
                ['id' => 1, 'title' => 'Movie 1'],
                ['id' => 2, 'title' => 'Movie 2']
            ],
            'page' => 1,
            'total_pages' => 10
        ]));

        $httpClient = new MockHttpClient($mockResponse);
        $cache = $this->createCacheMock();
        $service = new TmdbApiService($httpClient, 'fake_api_key', $cache);
        $result = $service->getPopularMovies(1);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('results', $result);
        $this->assertCount(2, $result['results']);
    }

    /**
     * Vérifie que getUpcomingMovies renvoie des films à venir.
     */
    public function testGetUpcomingMoviesReturnsResults(): void
    {
        $mockResponse = new MockResponse(json_encode([
            'results' => [
                ['id' => 1, 'title' => 'Upcoming Movie 1'],
                ['id' => 2, 'title' => 'Upcoming Movie 2']
            ],
            'page' => 1,
            'total_pages' => 5
        ]));

        $httpClient = new MockHttpClient($mockResponse);
        $cache = $this->createCacheMock();
        $service = new TmdbApiService($httpClient, 'fake_api_key', $cache);
        $result = $service->getUpcomingMovies(1);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('results', $result);
        $this->assertCount(2, $result['results']);
    }

    /**
     * Vérifie la récupération de films par genre.
     */
    public function testGetMoviesByGenreReturnsResults(): void
    {
        $mockResponse = new MockResponse(json_encode([
            'results' => [
                ['id' => 1, 'title' => 'Action Movie 1'],
                ['id' => 2, 'title' => 'Action Movie 2']
            ],
            'page' => 1,
            'total_pages' => 3
        ]));

        $httpClient = new MockHttpClient($mockResponse);
        $cache = $this->createCacheMock();
        $service = new TmdbApiService($httpClient, 'fake_api_key', $cache);
        $result = $service->getMoviesByGenre(28, 1);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('results', $result);
        $this->assertCount(2, $result['results']);
    }

    /**
     * Vérifie la récupération de la liste des genres.
     */
    public function testGetGenresReturnsGenreList(): void
    {
        $mockResponse = new MockResponse(json_encode([
            'genres' => [
                ['id' => 28, 'name' => 'Action'],
                ['id' => 35, 'name' => 'Comedy'],
                ['id' => 18, 'name' => 'Drama']
            ]
        ]));

        $httpClient = new MockHttpClient($mockResponse);
        $cache = $this->createCacheMock();
        $service = new TmdbApiService($httpClient, 'fake_api_key', $cache);
        $result = $service->getGenres();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('genres', $result);
        $this->assertCount(3, $result['genres']);
        $this->assertEquals('Action', $result['genres'][0]['name']);
    }

    /**
     * Teste la génération correcte d'une URL d'image TMDB.
     */
    public function testGetImageUrlReturnsCorrectUrl(): void
    {
        $httpClient = $this->createMock(\Symfony\Contracts\HttpClient\HttpClientInterface::class);
        $cache = $this->createCacheMock();
        $service = new TmdbApiService($httpClient, 'fake_api_key', $cache);
        
        $url = $service->getImageUrl('/abc123.jpg');
        $this->assertEquals('https://image.tmdb.org/t/p/w500/abc123.jpg', $url);

        $nullUrl = $service->getImageUrl(null);
        $this->assertNull($nullUrl);
    }

    /**
     * Vérifie qu'une recherche vide appelle bien l'API (pas d'erreur/boucle).
     */
    public function testEmptySearchQueryStillCallsApi(): void
    {
        $mockResponse = new MockResponse(json_encode([
            'results' => [],
            'total_pages' => 0
        ]));

        $httpClient = new MockHttpClient($mockResponse);
        $cache = $this->createCacheMock();
        $service = new TmdbApiService($httpClient, 'fake_api_key', $cache);
        
        $result = $service->searchMovies('');
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('results', $result);
    }
}