<?php

namespace Karhal\Web3ConnectBundle\EventListener;

use Karhal\Web3ConnectBundle\Controller\Web3ConnectController;
use Karhal\Web3ConnectBundle\Handler\RequestHandler;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;

final class RequestListener
{
    private array $contentTypes;

    public function __construct(array $contentTypes = ['json', 'jsonld'])
    {
        $this->contentTypes = $contentTypes;
    }

    public function onKernelController(ControllerEvent $event): void
    {
        $request = $event->getRequest();
        if (false === $this->supports($request) ||
            (is_array($event->getController())
            && get_class($event->getController()[0]) !== Web3ConnectController::class)
        ) {
            return;
        }
        try {
            RequestHandler::replaceJsonDataFromRequest($request);
        } catch (\JsonException $exception) {
            throw new BadRequestException($exception->getMessage());
        }

    }

    private function supports(Request $request): bool
    {
        return in_array($request->getContentType(), $this->contentTypes, true) && $request->getContent();
    }
}
