<?php

namespace Karhal\Web3ConnectBundle\Handler;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTHandler
{
    private array $_configuration;

    public function setConfiguration(array $configuration): void
    {
        $this->_configuration = $configuration;
    }

    public function createJWT(array $payload): string
    {
        $time = time();
        $payload['expiration'] = $time + ((int) $this->_configuration['ttl']);
        return JWT::encode(
            $payload,
            $this->_configuration['jwt_secret'],
            $this->_configuration['jwt_algo']
        );
    }

    public function decodeJWT(string $payload): array
    {
        return (array) JWT::decode(
            $payload,
            $this->getKey()
        );
    }

    private function getKey()
    {
        return new Key($this->_configuration['jwt_secret'], $this->_configuration['jwt_algo']);
    }
}
