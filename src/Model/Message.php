<?php

namespace Karhal\Web3ConnectBundle\Model;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Mapping\ClassMetadata;

final class Message
{
    protected string $address;
    protected string $statement;
    protected string $domain;
    protected string $uri;
    protected int $version;
    protected int $chainId;
    protected string $nonce;
    protected ?\DateTimeImmutable $issuedAt = null;
    protected ?\DateTimeImmutable $expirationTime = null;
    protected ?\DateTimeImmutable $notBefore = null;
    protected ?string $requestId = null;
    protected ?array $resources = null;

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): Message
    {
        $this->address = $address;

        return $this;
    }

    public function getStatement(): ?string
    {
        return $this->statement;
    }

    public function setStatement(?string $statement): Message
    {
        $this->statement = $statement;

        return $this;
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function setDomain(string $domain): Message
    {
        $this->domain = $domain;

        return $this;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function setUri(string $uri): Message
    {
        $this->uri = $uri;

        return $this;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function setVersion(int $version): Message
    {
        $this->version = $version;

        return $this;
    }

    public function getChainId(): int
    {
        return $this->chainId;
    }

    public function setChainId(int $chainId): Message
    {
        $this->chainId = $chainId;

        return $this;
    }

    public function getNonce(): string
    {
        return $this->nonce;
    }

    public function setNonce(string $nonce): Message
    {
        $this->nonce = $nonce;

        return $this;
    }

    public function getIssuedAt(): ?\DateTimeImmutable
    {
        return $this->issuedAt;
    }

    public function setIssuedAt(?\DateTimeImmutable $issuedAt): Message
    {
        $this->issuedAt = $issuedAt;

        return $this;
    }

    public function getExpirationTime(): ?\DateTimeImmutable
    {
        return $this->expirationTime;
    }

    public function setExpirationTime(?\DateTimeImmutable $expirationTime): Message
    {
        $this->expirationTime = $expirationTime;

        return $this;
    }

    public function getNotBefore(): ?\DateTimeImmutable
    {
        return $this->notBefore;
    }

    public function setNotBefore(?\DateTimeImmutable $notBefore): Message
    {
        $this->notBefore = $notBefore;

        return $this;
    }

    public function getRequestId(): ?string
    {
        return $this->requestId;
    }

    public function setRequestId(?string $requestId): Message
    {
        $this->requestId = $requestId;

        return $this;
    }


    public function getResources(): ?array
    {
        return $this->resources;
    }

    public function setResources(?array $resources): Message
    {
        $this->resources = $resources;

        return $this;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('address', new NotBlank());
        $metadata->addPropertyConstraint('address', new Regex('/^0x[a-fA-F0-9]{40}$/'));
//        $metadata->addPropertyConstraint('uri', new Url());
//        $metadata->addPropertyConstraint('chainId', new Type('integer'));
    }
}
