<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Propose des suggestions (films populaires) sur la page de recherche si aucun terme n'est saisi.
 */
class SearchSuggestionSubscriber implements EventSubscriberInterface
{
    /**
     * Suggestions par défaut affichées.
     */
    private const POPULAR_SEARCHES = [
        'Inception',
        'Interstellar',
        'The Matrix',
        'Pulp Fiction',
        'Fight Club',
        'The Shawshank Redemption',
    ];

    /**
     * Inscrit l'écouteur sur l'événement CONTROLLER.
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => ['onKernelController', 0],
        ];
    }

    /**
     * Ajoute les suggestions à la requête sur la page /movies/search si la recherche est vide.
     */
    public function onKernelController(ControllerEvent $event): void
    {
        $request = $event->getRequest();

        // Si route = 'movie_search' et query vide, on prépare les suggestions.
        if ($request->attributes->get('_route') === 'movie_search') {
            $query = $request->query->get('q', '');

            if (empty($query)) {
                $request->attributes->set('suggestions', self::POPULAR_SEARCHES);
            }
        }
    }
}