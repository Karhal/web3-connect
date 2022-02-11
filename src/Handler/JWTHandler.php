<?php

namespace Karhal\Web3ConnectBundle\Handler;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTHandler
{
    private array $_configuration;

    /**
     * @param array $configuration
     *
     * @return void
     */
    public function setConfiguration(array $configuration): void
    {
        $this->_configuration = $configuration;
    }

    /**
     * @param array $payload
     *
     * @return string
     */
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

    /**
     * @param string $payload
     *
     * @return array
     */
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
