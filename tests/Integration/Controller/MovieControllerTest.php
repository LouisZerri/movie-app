<?php

namespace App\Tests\Integration\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Tests d'intégration pour les pages du MovieController (/movies).
 */
class MovieControllerTest extends WebTestCase
{
    /**
     * La page d'accueil des films doit charger et afficher la liste des films populaires.
     */
    public function testIndexPageLoads(): void
    {
        $client = static::createClient();
        //$crawler = $client->request('GET', '/movies/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Films Populaires');
        $this->assertSelectorExists('.movies-grid');
    }

    /**
     * La page de recherche doit charger son formulaire.
     */
    public function testSearchPageLoads(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/movies/search');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form.search-form');
        $this->assertSelectorExists('input[name="q"]');
    }

    /**
     * La recherche avec un terme doit afficher des résultats.
     */
    public function testSearchWithQuery(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/movies/search?q=inception');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.section-header h2', 'Résultats pour');
    }

    /**
     * La recherche sans terme doit proposer des suggestions.
     */
    public function testSearchWithoutQueryShowsSuggestions(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/movies/search');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.suggestions-list');
    }

    /**
     * La page de détails d'un film doit charger correctement.
     */
    public function testMovieDetailsPageLoads(): void
    {
        $client = static::createClient();
        
        // Récupère le premier film de la page d'accueil
        $crawler = $client->request('GET', '/movies/');
        $this->assertResponseIsSuccessful();
        
        // Clique sur le lien du premier film pour accéder à sa fiche détail
        $link = $crawler->filter('.movie-card a.btn')->first()->link();
        $client->click($link);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.details-title');
        $this->assertSelectorExists('.details-poster');
    }

    /**
     * Vérifie que la page "Films à venir" charge et affiche le bon titre.
     */
    public function testUpcomingPageLoads(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/movies/upcoming');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Films à venir');
    }

    /**
     * Vérifie que la pagination fonctionne (présence de la pagination en page 2).
     */
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

    /**
     * Vérifie que les liens de navigation "À venir" et "Recherche" fonctionnent.
     */
    public function testNavigationLinksWork(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/movies/');

        // Clique sur le lien "À venir"
        $link = $crawler->selectLink('À venir')->link();
        $client->click($link);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Films à venir');

        // Retour à l'accueil
        $crawler = $client->request('GET', '/movies/');
        
        // Clique sur le lien "Recherche"
        $link = $crawler->selectLink('Recherche')->link();
        $client->click($link);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form.search-form');
    }
}