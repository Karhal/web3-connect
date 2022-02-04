<?php

namespace Karhal\Web3ConnectBundle\Tests\Handler;

use Elliptic\Curve\ShortCurve\Point;
use Illuminate\Support\Str;
use Karhal\Web3ConnectBundle\Handler\Web3WalletHandler;
use Karhal\Web3ConnectBundle\Model\Wallet;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validation;


class Web3WalletHandlerTest extends TestCase
{
    const ADDRESS = '0xedaab2a15961a7b6581f4f8c60b32f9bd8802f8c';
    const MESSAGE = 'You must be the change you want to see in the world';
    const SIGNATURE = '0x9b1d8d7fb288ffa72e3379910ec33a6a146c8bb5b57d5bab60dfd6fdc6aba65918658227d9f151404607873919310150a87796dc4914f5fb1319375f8f1a0d111c';
    const CONF = [
        'sign_message' => self::MESSAGE
    ];

    public function testGenerateNonce()
    {
        $string = Str::random();
        $handler = $this->createHandler();
        $nonce = $handler->generateNonce($string);
        $nonce2 = $handler->generateNonce($string);

        $this->assertIsString($nonce);
        $this->assertEquals($nonce2, $nonce);
    }

    public function testRecoverPublicKeyFromSignature()
    {
        $recoveredAddress = $this->createHandler()->recoverPublicKeyFromSignature('', self::SIGNATURE);
        $this->assertInstanceOf(Point::class, $recoveredAddress);
    }

    public function testAddressesMatch()
    {
        $this->assertTrue($this->createHandler()->checkSignature('', self::SIGNATURE, self::ADDRESS));
    }

    public function testCreateWallet()
    {
        $wallet = $this->createHandler()
            ->createWallet('0xAb5801a7D398351b8bE11C439e05C5B3259aeC9B', '0xFFFSignature');
        $this->assertInstanceOf(Wallet::class, $wallet);
    }

    private function createHandler(): Web3WalletHandler
    {
        $validator = Validation::createValidatorBuilder()
            ->getValidator();
        $handler = new Web3WalletHandler($validator);
        $handler->setConfiguration(self::CONF);

        return $handler;
    }
}