<?php

namespace Karhal\Web3ConnectBundle\Handler;

use Elliptic\EC;
use Karhal\Web3ConnectBundle\Exception\InvalidNonceException;
use kornrunner\Keccak;
use Illuminate\Support\Str;
use Elliptic\Curve\ShortCurve\Point;
use Karhal\Web3ConnectBundle\Model\Message;
use Karhal\Web3ConnectBundle\Exception\SignatureFailException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class Web3WalletHandler
{
    private array $_configuration;
    private ValidatorInterface $validator;
    private SessionInterface $session;
    private CacheInterface $cache;

    public function __construct(RequestStack $requestStack, ValidatorInterface $validator, CacheInterface $cache)
    {
        $this->session = $requestStack->getSession();
        $this->validator = $validator;
        $this->cache = $cache;
    }

    public function setConfiguration(array $configuration): void
    {
        $this->_configuration = $configuration;
    }

    public function generateNonce(?string $nonce = null): string
    {
        if (null === $nonce) {
            $nonce = Str::random(8);
        }

        return $this->cache->get($nonce, function (ItemInterface $item) use ($nonce) {
            $item->expiresAfter(10);
            return $nonce;
        });
    }

    public function getNonce(?string $nonce): string
    {
        return $this->cache->get($nonce, function (ItemInterface $item) use ($nonce) {
            return Str::random(8);
        });
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

    public function createMessageFromString(string $content): Message
    {
        $array = explode("\n", $content);

        $content = [];
        $content['address'] = $array[1];
        $content['domain'] = explode(' ', $array[0])[0];
        $content['issued-at'] = trim(explode('Issued At:', $array[9])[1]);
        $content['statement'] = $array[3];
        $content['uri'] = explode(' ', $array[5])[1];
        $content['version'] = (int)explode(':', $array[6])[1];
        $content['chain-id'] = (int)explode(':', $array[7])[1];
        $content['nonce'] = trim(explode(':', $array[8])[1]);

        return $this->createMessageFromArray($content);
    }

    public function createMessageFromArray(array $content): Message
    {
        $message = new Message();
        $message->setAddress($content['address']);
        $message->setStatement($content['statement']);
        $message->setChainId($content['chain-id']);
        $message->setVersion($content['version']);
        $message->setDomain($content['domain']);
        $message->setNonce(str_ireplace('"', '', trim($content['nonce'])));

        if (array_key_exists('issued-at', $content)) {
            $message->setIssuedAt(new \DateTimeImmutable($content['issued-at']));
        }

        if (array_key_exists('expiration-time', $content)) {
            $message->setIssuedAt(new \DateTimeImmutable($content['expiration-time']));
        }

        if (array_key_exists('not-before', $content)) {
            $message->setNotBefore(new \DateTimeImmutable($content['not-before']));
        }

        if (array_key_exists('request-id', $content)) {
            $message->setRequestId($content['request-id']);
        }

        if (array_key_exists('resources', $content)) {
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

    public function extractMessage(Request $request): Message
    {
        $input = $request->getContent();
        $content = \json_decode($input, true);

        if (is_string($content['message'])) {
            $message = $this->createMessageFromString($content['message']);
        } else {
            $message = $this->createMessageFromArray($content['message']);
        }

        if (!(($this->session->has('nonce') && $this->session->get('nonce') === $message->getNonce()) ||
            $this->getNonce($message->getNonce()) === $message->getNonce())) {
            throw new InvalidNonceException("Invalid Nonce");
        }
        $this->cache->delete($message->getNonce());

        return $message;
    }

    public function prepareMessage(Message $message): string
    {
        //todo https://eips.ethereum.org/EIPS/eip-4361#message-field-descriptions
        $header = "{$message->getDomain()} wants you to sign in with your Ethereum account:";
        $uri = "URI: {$message->getUri()}";
        $prefix = implode("\n", [$header, $message->getAddress()]);
        $version = "Version: {$message->getVersion()}";
        $chain = "Chain ID: {$message->getChainId()}";
        $nonce = "Nonce: {$message->getNonce()}";
        $suffixArray = [$uri, $version, $chain, $nonce];

        if ($message->getIssuedAt()) {
            $formattedDate = str_ireplace('+00:00', 'Z', $message->getIssuedAt()->format(DATE_RFC3339_EXTENDED));
            $issuedAt = "Issued At: {$formattedDate}";
            $suffixArray[] = $issuedAt;
        }

        if ($message->getExpirationTime()) {
            $expirationTime = "Expiration Time: {$message->getExpirationTime()->format('Y-m-d\TH:i:s\Z')}";
            $suffixArray[] = $expirationTime;
        }

        if ($message->getNotBefore()) {
            $notBefore = "Not Before: {$message->getNotBefore()->format('Y-m-d\TH:i:s\Z')}";
            $suffixArray[] = $notBefore;
        }

        if ($message->getRequestId()) {
            $requestId = "Request ID: {$message->getRequestId()}";
            $suffixArray[] = $requestId;
        }

        if ($message->getResources()) {
            $resources = implode("\n- ", $message->getResources());
            $suffixArray[] = "Resources:\n- ".$resources;
        }

        $suffix = implode("\n", $suffixArray);

        if (null !== $message->getStatement()) {
            $prefix = implode("\n\n", [$prefix, $message->getStatement()]);
        } else {
            $prefix .= "\n";
        }

        return implode("\n\n", [$prefix, $suffix]);
    }
}
