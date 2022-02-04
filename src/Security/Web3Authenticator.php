<?php

namespace Karhal\Web3ConnectBundle\Security;

use Karhal\Web3ConnectBundle\Handler\JWTHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\AuthenticationExpiredException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class Web3Authenticator extends AbstractAuthenticator
{
    private JWTHandler $JWThandler;
    private array $configuration;

    public function __construct(JWTHandler $JWTHandler)
    {
        $this->JWThandler = $JWTHandler;
    }

    public function setConfiguration(array $configuration)
    {
        $this->configuration = $configuration;
    }

    public function supports(Request $request): ?bool
    {
        return $request->headers->has($this->configuration['http_header']);
    }

    public function authenticate(Request $request): Passport
    {
        $payload = $this->JWThandler->decodeJWT($request->headers->get($this->configuration['http_header']));

        if ($payload['expiration'] < time()) {
            throw new AuthenticationExpiredException();
        }

        return new SelfValidatingPassport(
            new UserBadge(
                $payload['wallet'],
                function () use ($payload) {
                    return $this->loadUser($payload);
                }
            )
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $data = [
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData())
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @param $payload
     * @return UserInterface
     * @throws \Exception
     */
    private function loadUser($payload): UserInterface
    {
        if (!isset($payload['user'])) {
            throw new \Exception('Bad payload');
        }
        return unserialize($payload['user']);
    }
}
