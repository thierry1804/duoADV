<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Protège contre les tentatives de changement d'environnement via des paramètres de requête.
 * Cette vulnérabilité pourrait permettre à un attaquant de forcer l'application
 * à utiliser l'environnement de développement, exposant des informations sensibles.
 */
class EnvironmentProtectionListener implements EventSubscriberInterface
{
    private const HTTP_STATUS_FORBIDDEN = 403;

    public static function getSubscribedEvents(): array
    {
        return [
            // Priorité très élevée pour intercepter avant tout autre traitement
            KernelEvents::REQUEST => ['onKernelRequest', 2048],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        
        // Liste des paramètres sensibles qui ne doivent jamais être modifiables via la requête
        $sensitiveParams = ['APP_ENV', 'APP_DEBUG', 'SYMFONY_ENV', 'SYMFONY_DEBUG'];
        
        $violations = [];
        
        // Vérifie les paramètres de requête GET
        foreach ($sensitiveParams as $param) {
            if ($request->query->has($param)) {
                $violations[] = $param;
                // Supprime immédiatement le paramètre
                $request->query->remove($param);
            }
        }
        
        // Vérifie les paramètres de requête POST
        foreach ($sensitiveParams as $param) {
            if ($request->request->has($param)) {
                $violations[] = $param;
                // Supprime immédiatement le paramètre
                $request->request->remove($param);
            }
        }
        
        // Si des tentatives de manipulation ont été détectées, rejette la requête
        if (!empty($violations)) {
            $response = new Response(
                sprintf(
                    'Security violation: Attempt to modify environment variables via request parameters detected (%s).',
                    implode(', ', $violations)
                ),
                self::HTTP_STATUS_FORBIDDEN
            );
            
            $event->setResponse($response);
            $event->stopPropagation();
        }
    }
}



