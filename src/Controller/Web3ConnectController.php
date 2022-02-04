<?php

namespace Karhal\Web3ConnectBundle\Controller;

use Illuminate\Support\Str;
use Doctrine\Persistence\ManagerRegistry;
use Karhal\Web3ConnectBundle\Event\DataInitializedEvent;
use Karhal\Web3ConnectBundle\Exception\SignatureFailException;
use Karhal\Web3ConnectBundle\Handler\JWTHandler;
use Karhal\Web3ConnectBundle\Handler\Web3WalletHandler;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

class Web3ConnectController
{
    private ManagerRegistry $registry;
    private Web3WalletHandler $walletHandler;
    private array $configuration;
    private EventDispatcherInterface $eventDispatcher;
    private JWTHandler $JWThandler;

    public function __construct(ManagerRegistry $registry, Web3WalletHandler $walletHandler, EventDispatcherInterface $eventDispatcher, JWTHandler $JWThandler)
    {
        $this->registry = $registry;
        $this->walletHandler = $walletHandler;
        $this->eventDispatcher = $eventDispatcher;
        $this->JWThandler = $JWThandler;
    }

    public function setConfiguration(array $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @param  Request $request
     * @return JsonResponse
     */
    public function nonce(Request $request): JsonResponse
    {
        $request->getSession()->set('nonce', $nonce = Str::random());

        return new JsonResponse($this->walletHandler->generateNonce($nonce));
    }

    /**
     * @param  Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function verify(Request $request): JsonResponse
    {
        $wallet = $this->walletHandler->createWallet($request->get('address'), $request->get('signature'));

        if (!$this->walletHandler->checkSignature($request->getSession()->get('nonce'), $wallet->getSignature(), $wallet->getAddress())) {
            throw new SignatureFailException('Signature verification failed');
        }

        if (!$user = $this->registry->getRepository($this->configuration['user_class'])->findOneBy(['walletAddress' => $wallet->getAddress()])) {
            throw new UserNotFoundException('Unknown user.');
        }

        $event = new DataInitializedEvent();
        $this->eventDispatcher->dispatch($event, $event::NAME);

        $jwt = $this->JWThandler->createJWT(
            [
            'user' => \serialize($user),
            'wallet' => $wallet->getAddress(),
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
