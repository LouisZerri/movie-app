<?php

namespace App\Tests\Regression;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UiRegressionTest extends WebTestCase
{
    /**
     * Test que les éléments critiques de l'UI sont présents
     */
    public function testCriticalUiElementsExist(): void
    {
        $client = static::createClient();
        
        // Page d'accueil
        $crawler = $client->request('GET', '/movies/');
        
        // Header
        $this->assertSelectorExists('header');
        $this->assertSelectorExists('header h1');
        $this->assertSelectorExists('nav');
        $this->assertSelectorExists('nav a');
        
        // Contenu principal
        $this->assertSelectorExists('main');
        $this->assertSelectorExists('.section-header');
        $this->assertSelectorExists('.movies-grid');
        $this->assertSelectorExists('.movie-card');
        
        // Footer
        $this->assertSelectorExists('footer');
        
        // Pagination
        $this->assertSelectorExists('.pagination');
    }

    /**
     * Test que les classes CSS critiques sont présentes
     */
    public function testCriticalCssClassesArePresent(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/movies/');
        
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

    /**
     * Test que la structure HTML n'a pas changé
     */
    public function testHtmlStructureIntegrity(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/movies/');
        
        // Vérifier la structure header > container > h1
        $this->assertCount(1, $crawler->filter('header > .container > a > h1'));
        
        // Vérifier la structure main > container > .main-layout
        $this->assertCount(1, $crawler->filter('main > .container.main-layout'));
        
        // Vérifier que chaque movie-card a bien image + content + title + button
        $movieCards = $crawler->filter('.movie-card');
        if ($movieCards->count() > 0) {
            $firstCard = $movieCards->first();
            $this->assertCount(1, $firstCard->filter('.movie-card-image'));
            $this->assertCount(1, $firstCard->filter('.movie-card-content'));
            $this->assertCount(1, $firstCard->filter('.movie-card-title'));
            $this->assertCount(1, $firstCard->filter('.btn'));
        }
    }

    /**
     * Test que les routes critiques fonctionnent toujours
     */
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

    /**
     * Test que le formulaire de recherche fonctionne toujours
     */
    public function testSearchFormStillWorks(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/movies/search');
        
        // Vérifier que le formulaire existe
        $form = $crawler->selectButton('🔍 Rechercher')->form();
        $this->assertNotNull($form);
        
        // Soumettre une recherche
        $client->submit($form, ['q' => 'Matrix']);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.section-header', 'Résultats pour');
    }

    /**
     * Test que la sidebar des films récents fonctionne toujours
     */
    public function testRecentMoviesSidebarStillWorks(): void
    {
        $client = static::createClient();
        
        // Visiter un film
        $crawler = $client->request('GET', '/movies/');
        $link = $crawler->filter('.movie-card a.btn')->first()->link();
        $client->click($link);
        
        // Revenir à l'accueil
        $crawler = $client->request('GET', '/movies/');
        
        // Vérifier que la sidebar existe
        $this->assertSelectorExists('.recent-movies-widget');
        $this->assertSelectorExists('.recent-movie-item');
        $this->assertSelectorTextContains('.recent-movies-widget h3', 'Récemment consultés');
    }

    /**
     * Test de performance: la page doit se charger en moins de 3 secondes
     */
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