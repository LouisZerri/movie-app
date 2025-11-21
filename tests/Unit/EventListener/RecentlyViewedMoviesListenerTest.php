<?php

namespace App\Tests\Unit\EventListener;

use App\EventListener\RecentlyViewedMoviesListener;
use App\Service\TmdbApiService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Teste le listener qui gère la liste des films récemment consultés en session.
 */
class RecentlyViewedMoviesListenerTest extends TestCase
{
    private Session $session;
    private RequestStack $requestStack;
    private RecentlyViewedMoviesListener $listener;

    /**
     * Initialise une session simulée pour chaque test.
     */
    protected function setUp(): void
    {
        $this->session = new Session(new MockArraySessionStorage());
        $this->requestStack = new RequestStack();
        
        $request = new Request();
        $request->setSession($this->session);
        $this->requestStack->push($request);
    }

    /**
     * Crée un mock de TmdbApiService qui retourne des données prédéfinies.
     */
    private function createTmdbMock(array $returnValue): TmdbApiService
    {
        $mock = $this->getMockBuilder(TmdbApiService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getMovieDetails'])
            ->getMock();
        
        $mock->expects($this->any())
            ->method('getMovieDetails')
            ->willReturn($returnValue);
        
        return $mock;
    }

    /**
     * Un film affiché doit se retrouver ajouté dans la session (dans l'historique).
     */
    public function testListenerSavesMovieIdToSession(): void
    {
        $tmdbApiService = $this->createTmdbMock([
            'id' => 550,
            'title' => 'Fight Club',
            'poster_path' => '/path.jpg',
            'release_date' => '1999-10-15'
        ]);

        $this->listener = new RecentlyViewedMoviesListener(
            $this->requestStack,
            $tmdbApiService
        );

        $request = new Request();
        $request->setSession($this->session);
        $request->attributes->set('_route', 'movie_details');
        $request->attributes->set('id', 550);

        $kernel = $this->createMock(HttpKernelInterface::class);
        $event = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        $this->listener->onKernelRequest($event);

        $recentMovies = $this->session->get('recent_movies', []);
        $this->assertContains(550, $recentMovies);
    }

    /**
     * On ne garde pas plus de 5 films : l'ajout d'un 6ème écrase le plus ancien.
     */
    public function testListenerLimitsToMaximumFiveMovies(): void
    {
        $tmdbApiService = $this->createTmdbMock([
            'id' => 1,
            'title' => 'Test Movie',
            'poster_path' => '/path.jpg',
            'release_date' => '2020-01-01'
        ]);

        $this->listener = new RecentlyViewedMoviesListener(
            $this->requestStack,
            $tmdbApiService
        );

        $kernel = $this->createMock(HttpKernelInterface::class);

        // Ajoute 7 films pour vérifier le maximum de 5 conservés.
        for ($i = 1; $i <= 7; $i++) {
            $request = new Request();
            $request->setSession($this->session);
            $request->attributes->set('_route', 'movie_details');
            $request->attributes->set('id', $i);

            $event = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);
            $this->listener->onKernelRequest($event);
        }

        $recentMovies = $this->session->get('recent_movies', []);
        $this->assertCount(5, $recentMovies);
    }

    /**
     * Consulter à nouveau un film déjà dans la liste le replace en tête.
     */
    public function testListenerMovesExistingMovieToFront(): void
    {
        // Prétend qu'on a déjà vu ces 5 films, donc [1,2,3,4,5]
        $this->session->set('recent_movies', [1, 2, 3, 4, 5]);
        
        // Stock données fictives pour chaque film
        $this->session->set('movie_1', ['id' => 1, 'title' => 'Movie 1']);
        $this->session->set('movie_2', ['id' => 2, 'title' => 'Movie 2']);
        $this->session->set('movie_3', ['id' => 3, 'title' => 'Movie 3']);
        $this->session->set('movie_4', ['id' => 4, 'title' => 'Movie 4']);
        $this->session->set('movie_5', ['id' => 5, 'title' => 'Movie 5']);

        $tmdbApiService = $this->createTmdbMock([
            'id' => 3,
            'title' => 'Movie 3',
            'poster_path' => '/path.jpg',
            'release_date' => '2020-01-01'
        ]);

        $this->listener = new RecentlyViewedMoviesListener(
            $this->requestStack,
            $tmdbApiService
        );

        $request = new Request();
        $request->setSession($this->session);
        $request->attributes->set('_route', 'movie_details');
        $request->attributes->set('id', 3);

        $kernel = $this->createMock(HttpKernelInterface::class);
        $event = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        $this->listener->onKernelRequest($event);

        $recentMovies = $this->session->get('recent_movies', []);
        $this->assertEquals(3, $recentMovies[0]);
    }

    /**
     * Si la route n'est pas "movie_details", rien ne doit être modifié.
     */
    public function testListenerDoesNothingOnNonDetailsRoute(): void
    {
        $tmdbApiService = $this->createTmdbMock([]);

        $this->listener = new RecentlyViewedMoviesListener(
            $this->requestStack,
            $tmdbApiService
        );

        $request = new Request();
        $request->setSession($this->session);
        $request->attributes->set('_route', 'movie_index');

        $kernel = $this->createMock(HttpKernelInterface::class);
        $event = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        $this->listener->onKernelRequest($event);

        $recentMovies = $this->session->get('recent_movies', []);
        $this->assertEmpty($recentMovies);
    }

    /**
     * Aucun effet sur une sous-requête (SUB_REQUEST).
     */
    public function testListenerDoesNothingOnSubRequest(): void
    {
        $tmdbApiService = $this->createTmdbMock([]);

        $this->listener = new RecentlyViewedMoviesListener(
            $this->requestStack,
            $tmdbApiService
        );

        $request = new Request();
        $request->setSession($this->session);
        $request->attributes->set('_route', 'movie_details');
        $request->attributes->set('id', 550);

        $kernel = $this->createMock(HttpKernelInterface::class);
        $event = new RequestEvent($kernel, $request, HttpKernelInterface::SUB_REQUEST);

        $this->listener->onKernelRequest($event);

        $recentMovies = $this->session->get('recent_movies', []);
        $this->assertEmpty($recentMovies);
    }

    /**
     * Les données du film affiché sont aussi stockées dans la session (pour la sidebar).
     */
    public function testListenerStoresMovieDataInSession(): void
    {
        $movieData = [
            'id' => 550,
            'title' => 'Fight Club',
            'poster_path' => '/path.jpg',
            'release_date' => '1999-10-15'
        ];

        $tmdbApiService = $this->createTmdbMock($movieData);

        $this->listener = new RecentlyViewedMoviesListener(
            $this->requestStack,
            $tmdbApiService
        );

        $request = new Request();
        $request->setSession($this->session);
        $request->attributes->set('_route', 'movie_details');
        $request->attributes->set('id', 550);

        $kernel = $this->createMock(HttpKernelInterface::class);
        $event = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        $this->listener->onKernelRequest($event);

        // Vérifie que les données du film sont stockées pour la clé 'movie_550'
        $storedMovie = $this->session->get('movie_550');
        $this->assertNotNull($storedMovie);
        $this->assertEquals('Fight Club', $storedMovie['title']);
        $this->assertEquals('/path.jpg', $storedMovie['poster_path']);
    }
}