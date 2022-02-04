<?php

namespace Karhal\Web3ConnectBundle\Model;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Mapping\ClassMetadata;

final class Wallet
{
    protected ?string $address;

    protected ?string $signature;

    /**
     * @return string
     */
    public function getAddress(): ?string
    {
        return $this->address;
    }

    /**
     * @param  null|string $address
     * @return Wallet
     */
    public function setAddress(?string $address): Wallet
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getSignature(): ?string
    {
        return $this->signature;
    }

    /**
     * @param  null|string $signature
     * @return Wallet
     */
    public function setSignature(?string $signature): Wallet
    {
        $this->signature = $signature;

        return $this;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('address', new NotBlank());
        $metadata->addPropertyConstraint('address', new Regex('/^0x[a-fA-F0-9]{40}$/'));
        $metadata->addPropertyConstraint('signature', new NotBlank());
        $metadata->addPropertyConstraint('signature', new Regex('/^0x([A-Fa-f0-9]{130})$/'));
    }
}
