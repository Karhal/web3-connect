<?php

namespace Karhal\Web3ConnectBundle\Handler;

use Elliptic\EC;
use kornrunner\Keccak;
use Illuminate\Support\Str;
use Elliptic\Curve\ShortCurve\Point;
use Karhal\Web3ConnectBundle\Model\Message;
use Karhal\Web3ConnectBundle\Exception\SignatureFailException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Web3WalletHandler
{
    private array $_configuration;
    private ValidatorInterface $validator;
    private SessionInterface $session;

    public function __construct(SessionInterface $session, ValidatorInterface $validator)
    {
        $this->session = $session;
        $this->validator = $validator;
    }

    public function setConfiguration(array $configuration): void
    {
        $this->_configuration = $configuration;
    }

    public function generateNonce(): string
    {
        return Str::random(8);
    }

    public function checkSignature(string $message, string $signature, string $address): bool
    {
        $pubkey = $this->recoverPublicKeyFromSignature($message, $signature);

        return hash_equals(
            (string) Str::of($address)->after('0x')->lower(),
            substr(Keccak::hash(substr(hex2bin($pubkey->encode('hex')), 1), 256), 24)
        );
    }

    public function recoverPublicKeyFromSignature(string $message, string $signature): Point
    {
        $hash = $this->hashMessage($message);
        $sign   = ['r' => substr($signature, 2, 64), 's' => substr($signature, 66, 64)];
        $recid  = $this->getRecidFromSignature($signature);

        return (new EC('secp256k1'))->recoverPubKey($hash, $sign, $recid);
    }

    public function createMessage(array $content): Message
    {
        $message = new Message();
        $message->setAddress($content['address']);
        $message->setStatement($content['statement']);
        $message->setChainId($content['chain-id']);
        $message->setVersion($content['version']);
        $message->setDomain($content['domain']);
        $message->setNonce(trim($content['nonce']));

        if(array_key_exists('issued-at', $content)) {
            $message->setIssuedAt(new \DateTimeImmutable($content['issued-at']));
        }

        if(array_key_exists('expiration-time', $content)) {
            $message->setIssuedAt(new \DateTimeImmutable($content['expiration-time']));
        }

        if(array_key_exists('not-before', $content)) {
            $message->setNotBefore(new \DateTimeImmutable($content['not-before']));
        }

        if(array_key_exists('request-id', $content)) {
            $message->setRequestId($content['request-id']);
        }

        if(array_key_exists('resources', $content)) {
            $message->setResources($content['resources']);
        }

        $message->setUri($content['uri']);

        $errors = $this->validator->validate($message);

        if (count($errors) > 0) {
            throw new ValidationFailedException('Invalid input', $errors);
        }

        return $message;
    }

    public function getRecidFromSignature(string $signature): int
    {
        $recid = ord(hex2bin(substr($signature, 130, 2))) - 27;

        if ($recid != ($recid & 1)) {
            throw new SignatureFailException('Invalid Signature');
        }

        return $recid;
    }

    private function hashMessage(string $message): string
    {
        return Keccak::hash(sprintf("\x19Ethereum Signed Message:\n%s%s", strlen($message), $message), 256);
    }

    public function prepareMessage(Message $message): string
    {
        if($this->session->get('nonce') != $message->getNonce()) {
            //throw new \Exception("Invalid Nonce");
        }

        //todo https://eips.ethereum.org/EIPS/eip-4361#message-field-descriptions
        $header = "{$message->getDomain()} wants you to sign in with your Ethereum account:";
        $uri = $message->getUri();
        $prefix = implode("\n", [$header, $message->getAddress()]);
        $version = "Version: {$message->getVersion()}";
        $chain = "Chain ID: {$message->getChainId()}";
        $nonce = "Nonce: {$message->getNonce()}";
        $suffixArray = [$uri, $version, $chain, $nonce];

        if($message->getIssuedAt()) {
            $issuedAt = "Issued At: {$message->getIssuedAt()->format('Y-m-d\TH:i:s\Z')}";
            $suffixArray[] = $issuedAt;
        }

        if($message->getExpirationTime()) {
            $expirationTime = "Expiration Time: {$message->getExpirationTime()->format('Y-m-d\TH:i:s\Z')}";
            $suffixArray[] = $expirationTime;
        }

        if($message->getNotBefore()) {
            $notBefore = "Not Before: {$message->getNotBefore()->format('Y-m-d\TH:i:s\Z')}";
            $suffixArray[] = $notBefore;
        }

        if($message->getRequestId()) {
            $requestId = "Request ID: {$message->getRequestId()}";
            $suffixArray[] = $requestId;
        }

        if($message->getResources()) {
            $resources = implode("\n- ", $message->getResources());
            $suffixArray[] = "Resources:\n- ".$resources;
        }

        $suffix = implode("\n", $suffixArray);

        if(null !== $message->getStatement()) {
            $prefix = implode("\n\n", [$prefix, $message->getStatement()]);
        } else {
            $prefix .= "\n";
        }

        return implode("\n\n", [$prefix, $suffix]);
    }
}
