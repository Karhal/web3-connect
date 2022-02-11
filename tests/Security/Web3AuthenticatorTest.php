<?php

namespace Karhal\Web3ConnectBundle\Tests\Security;

use Karhal\Web3ConnectBundle\Handler\JWTHandler;
use Karhal\Web3ConnectBundle\Model\Web3User;
use Karhal\Web3ConnectBundle\Security\Web3Authenticator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\AuthenticationExpiredException;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Token\PostAuthenticationToken;

class Web3AuthenticatorTest extends TestCase
{
    public const KEY = '$ecr3t';
    public const ALGO = 'HS256';
    public const HTTP_HEADER = 'X-AUTH-WEB3TOKEN';
    public const ADDRESS = '0xedaab2a15961a7b6581f4f8c60b32f9bd8802f8c';

    public const CONF = [
        'jwt_secret' => self::KEY,
        'jwt_algo' => self::ALGO,
        'http_header' => self::HTTP_HEADER,
        'ttl' => 3600
    ];

    public function testSupports()
    {
        $request = new Request();
        $request->setMethod('GET');

        $authenticator = new Web3Authenticator($this->getJWTHandler());
        $authenticator->setConfiguration(self::CONF);
        $request->headers->set(self::HTTP_HEADER, 'lorem');
        $this->assertTrue($authenticator->supports($request));
        $request->headers->remove(self::HTTP_HEADER);
        $this->assertFalse($authenticator->supports($request));
    }

    public function testAuthenticate()
    {
        $request = new Request();
        $request->setMethod('GET');
        $payload = [
            'wallet' => self::ADDRESS
        ];

        $jwtHandler = $this->getJWTHandler();
        $encoded = $jwtHandler->createJWT($payload);
        $request->headers->set('X-AUTH-WEB3TOKEN', $encoded);
        $authenticator = new Web3Authenticator($jwtHandler);
        $authenticator->setConfiguration(self::CONF);
        $this->assertInstanceOf(Passport::class, $authenticator->authenticate($request));

        $jwtHandler->setConfiguration(array_replace(self::CONF, ['ttl' => -1]));
        $encoded = $jwtHandler->createJWT($payload);
        $request->headers->set('X-AUTH-WEB3TOKEN', $encoded);
        $this->expectException(AuthenticationExpiredException::class);
        $authenticator->authenticate($request);
    }

    public function testLoadUser()
    {
        $user = new Web3User();
        $obj = new Web3Authenticator($this->getJWTHandler());
        $authenticator = new \ReflectionClass($obj);
        $method = $authenticator->getMethod('loadUser');
        $method->setAccessible(true);
        $this->assertInstanceOf(Web3User::class, $method->invokeArgs($obj, [['user' => \serialize($user)]]));
    }

    public function testOnAuthenticationSuccess()
    {
        $authenticator = new Web3Authenticator($this->getJWTHandler());
        $request = new Request([], ['foo' => 'bar'], [], [], [], []);
        $token = new PostAuthenticationToken(new Web3User(), 'api', []);
        $this->assertNull($authenticator->onAuthenticationSuccess($request, $token, 'api'));
    }

    public function testOnAuthenticationFailure()
    {
        $authenticator = new Web3Authenticator($this->getJWTHandler());
        $request = new Request([], ['foo' => 'bar'], [], [], [], []);
        $this->assertInstanceOf(JsonResponse::class, $authenticator->onAuthenticationFailure($request, new AuthenticationException()));
    }

    private function getJWTHandler(): JWTHandler
    {
        $jwtHandler = new JWTHandler();
        $jwtHandler->setConfiguration(self::CONF);

        return $jwtHandler;
    }
}
