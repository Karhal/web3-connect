<?php

namespace Karhal\Web3ConnectBundle\Tests\Handler;

use Karhal\Web3ConnectBundle\Handler\MessageHandler;
use Karhal\Web3ConnectBundle\Model\Message;
use PHPUnit\Framework\TestCase;

class MessageHandlerTest extends TestCase
{
    public const EIP4361_MESSAGE = '{"message":"127.0.0.1:8080 wants you to sign in with your Ethereum account:\n0x42fo56bEFooCA5ffFooC9cFE16Bar9o83fa0Baro\n\nSign in with Ethereum to the app.\n\nURI: https://service.org/login?foo=bar\nVersion: 1\nChain ID: 1\nNonce: RSbJtQh7\nIssued At: 2022-02-20T12:04:48.731Z\nExpiration Time: 2022-02-20T12:04:48.731Z\nNot Before: 2022-02-20T12:04:48.731Z\nRequest ID: rffefer\nResources:\n- ipfs://bafybeiemxf5abjwjbikoz4mc3a3dla6ual3jsgpdr4cjr3oz3evfyavhwq/\n- https://example.com/my-web2-claim.json","signature":"..."}';
    public function testParseMessage()
    {
        $input = \json_decode(self::EIP4361_MESSAGE, true);
        $handler = new MessageHandler();
        $message = $handler::parseMessage($input['message']);
        $this->assertInstanceOf(Message::class, $message);
        $this->assertEquals($message->getAddress(), '0x42fo56bEFooCA5ffFooC9cFE16Bar9o83fa0Baro');
        $this->assertEquals($message->getStatement(), 'Sign in with Ethereum to the app.');
        $this->assertEquals($message->getDomain(), '127.0.0.1:8080');
        $this->assertEquals($message->getUri(), 'https://service.org/login?foo=bar');
        $this->assertEquals($message->getVersion(), 1);
        $this->assertEquals($message->getChainId(), 1);
        $this->assertEquals($message->getNonce(), 'RSbJtQh7');
        $this->assertEquals($message->getIssuedAt(), new \DateTimeImmutable('2022-02-20 12:04:48.731 +00:00'));
        $this->assertEquals($message->getExpirationTime(), new \DateTimeImmutable('2022-02-20 12:04:48.731 +00:00'));
        $this->assertEquals($message->getNotBefore(), new \DateTimeImmutable('2022-02-20 12:04:48.731 +00:00'));
        $this->assertEquals($message->getRequestId(), 'rffefer');
        $this->assertEquals($message->getResources(), ['ipfs://bafybeiemxf5abjwjbikoz4mc3a3dla6ual3jsgpdr4cjr3oz3evfyavhwq/', 'https://example.com/my-web2-claim.json']);

    }
}
