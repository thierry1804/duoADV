<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Protège contre les attaques DoS en limitant le nombre d'en-têtes HTTP acceptés.
 */
class HttpHeadersLimitListener implements EventSubscriberInterface
{
    private const DEFAULT_MAX_HEADERS = 100;
    private const HTTP_STATUS_TOO_MANY_HEADERS = 431; // Request Header Fields Too Large

    public function __construct(
        private readonly int $maxHeaders = self::DEFAULT_MAX_HEADERS
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // Priorité élevée pour intercepter avant les autres listeners
            KernelEvents::REQUEST => ['onKernelRequest', 1024],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $headersCount = count($request->headers->all());

        if ($headersCount > $this->maxHeaders) {
            $response = new Response(
                sprintf(
                    'Too many HTTP headers. Maximum allowed: %d, received: %d.',
                    $this->maxHeaders,
                    $headersCount
                ),
                self::HTTP_STATUS_TOO_MANY_HEADERS
            );

            $event->setResponse($response);
            $event->stopPropagation();
        }
    }
}

