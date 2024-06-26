<?php

namespace Karhal\Web3ConnectBundle\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Karhal\Web3ConnectBundle\Event\DataInitializedEvent;
use Karhal\Web3ConnectBundle\Exception\SignatureFailException;
use Karhal\Web3ConnectBundle\Handler\JWTHandler;
use Karhal\Web3ConnectBundle\Handler\MessageHandler;
use Karhal\Web3ConnectBundle\Handler\Web3WalletHandler;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Contracts\Cache\CacheInterface;

class Web3ConnectController
{
    private ManagerRegistry $registry;
    private Web3WalletHandler $walletHandler;
    private array $configuration;
    private EventDispatcherInterface $eventDispatcher;
    private JWTHandler $JWThandler;
    private CacheInterface $cache;

    public function __construct(ManagerRegistry $registry, Web3WalletHandler $walletHandler, EventDispatcherInterface $eventDispatcher, JWTHandler $JWThandler, CacheInterface $cache)
    {
        $this->registry = $registry;
        $this->walletHandler = $walletHandler;
        $this->eventDispatcher = $eventDispatcher;
        $this->JWThandler = $JWThandler;
        $this->cache = $cache;
    }

    public function setConfiguration(array $configuration)
    {
        $this->configuration = $configuration;
    }

    //todo: Add address arg then store in cache key:addr, value:nonce

    public function nonce(Request $request, string $address): Response
    {
        $nonce = $this->walletHandler->generateNonce($address);

        return new JsonResponse(['nonce' => $nonce]);
    }

    public function verify(Request $request): JsonResponse
    {
        $input = $request->getContent();
        $content = \json_decode($input, true);
        $message = MessageHandler::parseMessage($content['message']);
        $signature =  json_decode($input, true)['signature'];

        $rawMessage = $this->walletHandler->prepareMessage($message);

        if (!$this->walletHandler->checkSignature($rawMessage, $signature, $message->getAddress())) {
            throw new SignatureFailException('Signature verification failed');
        }

        if (!$user = $this->registry->getRepository($this->configuration['user_class'])->findOneBy(['walletAddress' => $message->getAddress()])) {
            throw new UserNotFoundException('Unknown user.');
        }

        $event = new DataInitializedEvent();
        $this->eventDispatcher->dispatch($event, $event::NAME);

        $jwt = $this->JWThandler->createJWT(
            [
            'user' => \serialize($user),
            'wallet' => $message->getAddress(),
            ]
        );

        return new JsonResponse(
            [
            'identifier' => $user->getUserIdentifier(),
            'token' => $jwt,
            'data' => $event->getData()
            ]
        );
    }
}
