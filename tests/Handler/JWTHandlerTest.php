<?php

namespace Karhal\Web3ConnectBundle\Tests\Handler;

use Firebase\JWT\Key;
use Karhal\Web3ConnectBundle\Handler\JWTHandler;
use PHPUnit\Framework\TestCase;
use Firebase\JWT\JWT;

class JWTHandlerTest extends TestCase
{
    const KEY = '$ecr3t';
    const ALGO = 'HS256';
    const PAYLOAD = [
        'foo' => 'bar'
    ];
    const CONF = [
        'jwt_secret' => self::KEY,
        'jwt_algo' => self::ALGO,
        'ttl' => 0,
    ];

    public function testCreateJWT()
    {
        $handler = new JWTHandler();
        $handler->setConfiguration(self::CONF);
        $token = $handler->createJWT(self::PAYLOAD);
        $key = new Key(self::KEY, self::ALGO);
        $decoded = JWT::decode($token, $key);
        $this->assertTrue(property_exists($decoded, 'foo'));
        $this->assertTrue(get_object_vars($decoded)['foo'] === 'bar');
    }

    public function testDecodeJWT()
    {
        $handler = new JWTHandler();
        $handler->setConfiguration(self::CONF);
        $encoded = JWT::encode(self::PAYLOAD, self::KEY, self::ALGO);
        $decoded = $handler->decodeJWT($encoded);
        $this->assertEquals($decoded, self::PAYLOAD);
    }
}
