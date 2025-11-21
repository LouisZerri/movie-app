<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class SearchSuggestionSubscriber implements EventSubscriberInterface
{
    private const POPULAR_SEARCHES = [
        'Inception',
        'Interstellar',
        'The Matrix',
        'Pulp Fiction',
        'Fight Club',
        'The Shawshank Redemption',
    ];

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => ['onKernelController', 0],
        ];
    }

    public function onKernelController(ControllerEvent $event): void
    {
        $request = $event->getRequest();
        
        // Si on est sur la page de recherche sans query
        if ($request->attributes->get('_route') === 'movie_search') {
            $query = $request->query->get('q', '');
            
            // Si pas de recherche, ajouter des suggestions dans la requête
            if (empty($query)) {
                $request->attributes->set('suggestions', self::POPULAR_SEARCHES);
            }
        }
    }
}