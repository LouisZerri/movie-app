<?php

namespace App\Tests\Integration\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MovieControllerTest extends WebTestCase
{
    public function testIndexPageLoads(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/movies/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Films Populaires');
        $this->assertSelectorExists('.movies-grid');
    }

    public function testSearchPageLoads(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/movies/search');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form.search-form');
        $this->assertSelectorExists('input[name="q"]');
    }

    public function testSearchWithQuery(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/movies/search?q=inception');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.section-header h2', 'Résultats pour');
    }

    public function testSearchWithoutQueryShowsSuggestions(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/movies/search');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.suggestions-list');
    }

    public function testMovieDetailsPageLoads(): void
    {
        $client = static::createClient();
        
        // D'abord récupérer un film depuis la page d'accueil
        $crawler = $client->request('GET', '/movies/');
        $this->assertResponseIsSuccessful();
        
        // Cliquer sur le premier film (si disponible)
        $link = $crawler->filter('.movie-card a.btn')->first()->link();
        $client->click($link);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.details-title');
        $this->assertSelectorExists('.details-poster');
    }

    public function testUpcomingPageLoads(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/movies/upcoming');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Films à venir');
    }

    public function testPaginationWorks(): void
    {
        $client = static::createClient();
        
        // Page 1
        $crawler = $client->request('GET', '/movies/?page=1');
        $this->assertResponseIsSuccessful();
        
        // Page 2
        $crawler = $client->request('GET', '/movies/?page=2');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.pagination');
    }

    public function testNavigationLinksWork(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/movies/');

        // Tester le lien "À venir"
        $link = $crawler->selectLink('À venir')->link();
        $client->click($link);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Films à venir');

        // Revenir à l'accueil
        $crawler = $client->request('GET', '/movies/');
        
        // Tester le lien "Recherche"
        $link = $crawler->selectLink('Recherche')->link();
        $client->click($link);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form.search-form');
    }
}