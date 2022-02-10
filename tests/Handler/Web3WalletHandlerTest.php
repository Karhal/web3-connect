<?php

namespace Karhal\Web3ConnectBundle\Tests\Handler;

use Elliptic\Curve\ShortCurve\Point;
use Karhal\Web3ConnectBundle\Exception\SignatureFailException;
use Karhal\Web3ConnectBundle\Handler\Web3WalletHandler;
use Karhal\Web3ConnectBundle\Model\Message;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Validator\Validation;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class Web3WalletHandlerTest extends TestCase
{
    const NONCE = 'N4EkiSkl';
    const ADDRESS = '0x42d16fbE856CA5fDCD3C9cFE1672d9183fa01534';
    const SIG = '0xd0c7fb9d41405d865a401f5dfc96a52e1acdbc897b5ec52b6374c94decaf0f237d91c235cbd62276fb65e38b64aef6d5ce77398d879b9a1650a211c6529259c11b';
    const EIP4361_MESSAGE = '{
    "message": "localhost:8080 wants you to sign in with your Ethereum account:\n0x42d16fbE856CA5fDCD3C9cFE1672d9183fa01534\n\nSign in with Ethereum to the app.\n\nURI: http://localhost:8080\nVersion: 1\nChain ID: 1\nNonce: N4EkiSkl\nIssued At: 2022-02-10T11:07:24.835Z",
    "signature": "'.self::SIG.'"
}';
    const JSON_MESSAGE = '{
    "message": {
        "domain": "www.example.com",
        "uri": "http://127.0.0.1:8000/web3_login",
        "version": 1,
        "chain-id": 1,
        "nonce": "VlMdgoW1",
        "address": "0xedaab2a15961a7b6581f4f8c60b32f9bd8802f8c",
        "statement": "Sign in with Ethereum to the app.",
        "request-id": "foo",
        "resources": [
            "http://127.0.0.1",
            "http://127.0.0.2"
        ]
    },
    "signature": "0x11afc648826543977217e3cb0eab36e4f8197b908ef3396890d6712961b4d74a2809c624b43dca1447eace1b258fae95bd95f53f380ae9b327162390d2d5a4601b"
}';

    public function testGenerateNonce()
    {
        $handler = $this->createHandler();
        $nonce = $handler->generateNonce();
        $nonce2 = $handler->generateNonce();

        $this->assertIsString($nonce);
        $this->assertNotEquals($nonce2, $nonce);
    }

    public function testRecoverPublicKeyFromSignature()
    {
        $handler = $this->createHandler();
        $data = \json_decode(self::EIP4361_MESSAGE, true);
        $message = $handler->createMessageFromString($data['message']);
        $recoveredAddress = $this->createHandler()->recoverPublicKeyFromSignature($handler->prepareMessage($message), $data['signature']);
        $this->assertInstanceOf(Point::class, $recoveredAddress);
    }

    public function testAddressesMatch()
    {
        $handler = $this->createHandler();
        $data = \json_decode(self::EIP4361_MESSAGE, true);
        $message = $handler->createMessageFromString($data['message']);
        $this->assertTrue($this->createHandler()->checkSignature($handler->prepareMessage($message), $data['signature'], $message->getAddress()));
    }

    public function testCreateMessageFromString()
    {
        $handler = $this->createHandler();
        $data = \json_decode(self::EIP4361_MESSAGE, true);
        $message = $handler->createMessageFromString($data['message']);
        $this->assertInstanceOf(Message::class, $message);
    }

    public function testGetRecidFromSignature()
    {
        $this->expectException(SignatureFailException::class);
        $handler = $this->createHandler();
        $handler->getRecidFromSignature(self::ADDRESS);
        $this->assertIsInt($handler->getRecidFromSignature(self::SIG));
    }

    public function testCreateMessageFromArray()
    {
        $handler = $this->createHandler();
        $data = \json_decode(self::JSON_MESSAGE, true);
        $message = $handler->createMessageFromArray($data['message']);
        $this->assertInstanceOf(Message::class, $message);
    }

    public function testPrepareMessage()
    {
        $handler = $this->createHandler();
        $data = \json_decode(self::EIP4361_MESSAGE, true);
        $message = $handler->createMessageFromString($data['message']);
        $plainTextMessage = $handler->prepareMessage($message);
        $this->assertIsString($plainTextMessage);

        $data = \json_decode(self::JSON_MESSAGE, true);
        $message = $handler->createMessageFromArray($data['message']);
        $plainTextMessage = $handler->prepareMessage($message);
        $this->assertIsString($plainTextMessage);
    }

    public function testExtractMessage()
    {
        $request = new Request([], [], [], [], [], [], self::EIP4361_MESSAGE);
        $handler = $this->createHandler();
        $message = $handler->extractMessage($request);
        $this->assertInstanceOf(Message::class, $message);
    }

    private function createHandler(): Web3WalletHandler
    {
        $session = new Session(new MockArraySessionStorage());
        $session->set('nonce', self::NONCE);
        $validator = Validation::createValidatorBuilder()
            ->getValidator();

        return new Web3WalletHandler($session, $validator);
    }
}