<?php

namespace Karhal\Web3ConnectBundle\Tests\Handler;

use Elliptic\Curve\ShortCurve\Point;
use Karhal\Web3ConnectBundle\Exception\SignatureFailException;
use Karhal\Web3ConnectBundle\Handler\Web3WalletHandler;
use Karhal\Web3ConnectBundle\Model\Message;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Validator\Validation;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class Web3WalletHandlerTest extends TestCase
{
    const NONCE = 'VlMdgoW1';
    const ADDRESS = '0xedaab2a15961a7b6581f4f8c60b32f9bd8802f8c';
    const SIG = '0x11afc648826543977217e3cb0eab36e4f8197b908ef3396890d6712961b4d74a2809c624b43dca1447eace1b258fae95bd95f53f380ae9b327162390d2d5a4601b';
    const EIP4361_MESSAGE = '{
    "message": {
        "domain": "www.example.com",
        "uri": "http://127.0.0.1:8000/web3_login",
        "version": 1,
        "chain-id": 1,
        "nonce": "'.self::NONCE.'",
        "address": "'.self::ADDRESS.'",
        "statement": "Sign in with Ethereum to the app."
    },
    "signature": "'.self::SIG.'"
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
        $message = $handler->createMessage($data['message']);
        $recoveredAddress = $this->createHandler()->recoverPublicKeyFromSignature($handler->prepareMessage($message), $data['signature']);
        $this->assertInstanceOf(Point::class, $recoveredAddress);
    }

    public function testAddressesMatch()
    {
        $handler = $this->createHandler();
        $data = \json_decode(self::EIP4361_MESSAGE, true);
        $message = $handler->createMessage($data['message']);
        $this->assertTrue($this->createHandler()->checkSignature($handler->prepareMessage($message), $data['signature'], $message->getAddress()));
    }

    public function testCreateMessage()
    {
        $handler = $this->createHandler();
        $data = \json_decode(self::EIP4361_MESSAGE, true);
        $message = $handler->createMessage($data['message']);
        $this->assertInstanceOf(Message::class, $message);
    }

    public function testGetRecidFromSignature()
    {
        $this->expectException(SignatureFailException::class);
        $handler = $this->createHandler();
        $handler->getRecidFromSignature(self::ADDRESS);
        $this->assertIsInt($handler->getRecidFromSignature(self::SIG));
    }

    public function testPrepareMessage()
    {
        $handler = $this->createHandler();
        $data = \json_decode(self::EIP4361_MESSAGE, true);
        $message = $handler->createMessage($data['message']);
        $plainTextMessage = $handler->prepareMessage($message);

        $this->assertIsString($plainTextMessage);
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