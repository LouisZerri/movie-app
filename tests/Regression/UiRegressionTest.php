<?php

namespace App\Tests\Regression;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Tests de régression UI critiques (structure, accessibilité et performances).
 */
class UiRegressionTest extends WebTestCase
{
    /** Vérifie la présence des principaux blocs et éléments de la page d'accueil */
    public function testCriticalUiElementsExist(): void
    {
        $client = static::createClient();
        //$crawler = $client->request('GET', '/movies/');

        // Blocs principaux
        $this->assertSelectorExists('header');
        $this->assertSelectorExists('header h1');
        $this->assertSelectorExists('nav');
        $this->assertSelectorExists('nav a');
        $this->assertSelectorExists('main');
        $this->assertSelectorExists('.section-header');
        $this->assertSelectorExists('.movies-grid');
        $this->assertSelectorExists('.movie-card');
        $this->assertSelectorExists('footer');
        $this->assertSelectorExists('.pagination');
    }

    /** Vérifie que les classes CSS critiques sont présentes dans le HTML */
    public function testCriticalCssClassesArePresent(): void
    {
        $client = static::createClient();
        //$crawler = $client->request('GET', '/movies/');

        $criticalClasses = [
            '.container',
            '.main-layout',
            '.main-content',
            '.movies-grid',
            '.movie-card',
            '.movie-card-image',
            '.movie-card-content',
            '.movie-card-title',
            '.movie-card-meta',
            '.btn',
            '.pagination'
        ];

        foreach ($criticalClasses as $class) {
            $this->assertSelectorExists($class, "La classe $class doit exister");
        }
    }

    /** Vérifie la structure HTML critique du layout général et d'une carte film */
    public function testHtmlStructureIntegrity(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/movies/');

        // Quelques tests de structure imbriquée
        $this->assertCount(1, $crawler->filter('header > .container > a > h1'));
        $this->assertCount(1, $crawler->filter('main > .container.main-layout'));

        $movieCards = $crawler->filter('.movie-card');
        if ($movieCards->count() > 0) {
            $firstCard = $movieCards->first();
            $this->assertCount(1, $firstCard->filter('.movie-card-image'));
            $this->assertCount(1, $firstCard->filter('.movie-card-content'));
            $this->assertCount(1, $firstCard->filter('.movie-card-title'));
            $this->assertCount(1, $firstCard->filter('.btn'));
        }
    }

    /** Vérifie que les routes principales renvoient bien une réponse OK */
    public function testCriticalRoutesAreAccessible(): void
    {
        $client = static::createClient();

        $routes = [
            '/movies/',
            '/movies/search',
            '/movies/upcoming',
            '/movies/?page=1',
            '/movies/?page=2',
        ];

        foreach ($routes as $route) {
            $client->request('GET', $route);
            $this->assertResponseIsSuccessful("La route $route doit être accessible");
        }
    }

    /** Vérifie le fonctionnement du formulaire de recherche */
    public function testSearchFormStillWorks(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/movies/search');

        $form = $crawler->selectButton('🔍 Rechercher')->form();
        $this->assertNotNull($form);

        $client->submit($form, ['q' => 'Matrix']);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.section-header', 'Résultats pour');
    }

    /** Vérifie que la sidebar des films récemment consultés s'affiche et se peuple */
    public function testRecentMoviesSidebarStillWorks(): void
    {
        $client = static::createClient();

        // Simuler une consultation de film puis retour accueil
        $crawler = $client->request('GET', '/movies/');
        $link = $crawler->filter('.movie-card a.btn')->first()->link();
        $client->click($link);
        $crawler = $client->request('GET', '/movies/');

        $this->assertSelectorExists('.recent-movies-widget');
        $this->assertSelectorExists('.recent-movie-item');
        $this->assertSelectorTextContains('.recent-movies-widget h3', 'Récemment consultés');
    }

    /** Vérifie que la page d'accueil des films se charge rapidement */
    public function testPageLoadPerformance(): void
    {
        $client = static::createClient();

        $start = microtime(true);
        $client->request('GET', '/movies/');
        $duration = microtime(true) - $start;

        $this->assertLessThan(3, $duration, "La page doit se charger en moins de 3 secondes");
        $this->assertResponseIsSuccessful();
    }
}