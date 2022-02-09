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

    /**
     * @return string
     */
    public function getAddress(): ?string
    {
        return $this->address;
    }

    /**
     * @param  null|string $address
     * @return Message
     */
    public function setAddress(?string $address): Message
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getStatement(): ?string
    {
        return $this->statement;
    }

    /**
     * @param string|null $statement
     * @return $this
     */
    public function setStatement(?string $statement): Message
    {
        $this->statement = $statement;

        return $this;
    }

    /**
     * @return string
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * @param string $domain
     * @return Message
     */
    public function setDomain(string $domain): Message
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * @return string
     */
    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * @param string $uri
     * @return Message
     */
    public function setUri(string $uri): Message
    {
        $this->uri = $uri;

        return $this;
    }

    /**
     * @return int
     */
    public function getVersion(): int
    {
        return $this->version;
    }

    /**
     * @param int $version
     * @return Message
     */
    public function setVersion(int $version): Message
    {
        $this->version = $version;

        return $this;
    }

    /**
     * @return int
     */
    public function getChainId(): int
    {
        return $this->chainId;
    }

    /**
     * @param int $chainId
     * @return Message
     */
    public function setChainId(int $chainId): Message
    {
        $this->chainId = $chainId;

        return $this;
    }

    /**
     * @return string
     */
    public function getNonce(): string
    {
        return $this->nonce;
    }

    /**
     * @param string $nonce
     * @return Message
     */
    public function setNonce(string $nonce): Message
    {
        $this->nonce = $nonce;

        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getIssuedAt(): ?\DateTimeImmutable
    {
        return $this->issuedAt;
    }

    /**
     * @param \DateTimeImmutable|null $issuedAt
     * @return $this
     */
    public function setIssuedAt(?\DateTimeImmutable $issuedAt): Message
    {
        $this->issuedAt = $issuedAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getExpirationTime(): ?\DateTime
    {
        return $this->expirationTime;
    }

    /**
     * @param \DateTime|null $expirationTime
     * @return $this
     */
    public function setExpirationTime(?\DateTime $expirationTime): Message
    {
        $this->expirationTime = $expirationTime;

        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getNotBefore(): ?\DateTimeImmutable
    {
        return $this->notBefore;
    }

    /**
     * @param \DateTimeImmutable|null $notBefore
     * @return $this
     */
    public function setNotBefore(?\DateTimeImmutable $notBefore): Message
    {
        $this->notBefore = $notBefore;

        return $this;
    }

    /**
     * @return string
     */
    public function getRequestId(): ?string
    {
        return $this->requestId;
    }

    /**
     * @param string|null $requestId
     * @return $this
     */
    public function setRequestId(?string $requestId): Message
    {
        $this->requestId = $requestId;

        return $this;
    }

    /**
     * @return array
     */
    public function getResources(): ?array
    {
        return $this->resources;
    }

    /**
     * @param array|null $resources
     * @return $this
     */
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
