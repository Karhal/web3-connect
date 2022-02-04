<?php

namespace Karhal\Web3ConnectBundle\Handler;

use Elliptic\EC;
use kornrunner\Keccak;
use Illuminate\Support\Str;
use Elliptic\Curve\ShortCurve\Point;
use Karhal\Web3ConnectBundle\Model\Wallet;
use Karhal\Web3ConnectBundle\Exception\SignatureFailException;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Web3WalletHandler
{
    private array $_configuration;
    private ValidatorInterface $validator;

    /**
     * @param ValidatorInterface $validator
     */
    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

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
     * @param  string $random
     * @return string
     */
    public function generateNonce(string $random = ''): string
    {
        return "{$this->_configuration['sign_message']}$random";
    }

    /**
     * @param  string $nonce
     * @param  string $signature
     * @param  string $address
     * @return bool
     * @throws \Exception
     */
    public function checkSignature(string $nonce, string $signature, string $address): bool
    {
        $pubkey = $this->recoverPublicKeyFromSignature($nonce, $signature);

        return hash_equals(
            (string) Str::of($address)->after('0x')->lower(),
            substr(Keccak::hash(substr(hex2bin($pubkey->encode('hex')), 1), 256), 24)
        );
    }

    /**
     * @param  string $nonce
     * @param  string $signature
     * @return Point
     * @throws SignatureFailException
     */
    public function recoverPublicKeyFromSignature(string $nonce, string $signature): Point
    {
        $message = $this->generateNonce($nonce);
        $hash = $this->hashMessage($message);
        $sign   = ['r' => substr($signature, 2, 64), 's' => substr($signature, 66, 64)];
        $recid  = $this->getRecidFromSignature($signature);

        return (new EC('secp256k1'))->recoverPubKey($hash, $sign, $recid);
    }

    /**
     * @param string|null $address
     * @param string|null $signature
     * @return Wallet
     */
    public function createWallet(?string $address, ?string $signature): Wallet
    {
        $wallet = new Wallet();
        $wallet->setAddress($address);
        $wallet->setSignature($signature);
        $errors = $this->validator->validate($wallet);

        if (count($errors) > 0) {
            throw new ValidationFailedException('Invalid input', $errors);
        }

        return $wallet;
    }

    /**
     * @param  string $string
     * @return int
     * @throws SignatureFailException
     */
    private function getRecidFromSignature(string $string): int
    {
        $recid = ord(hex2bin(substr($string, 130, 2))) - 27;

        if ($recid != ($recid & 1)) {
            throw new SignatureFailException('Invalid Signature');
        }

        return $recid;
    }

    /**
     * @param  string $message
     * @return string
     * @throws \Exception
     */
    private function hashMessage(string $message): string
    {
        return Keccak::hash(sprintf("\x19Ethereum Signed Message:\n%s%s", strlen($message), $message), 256);
    }
}
