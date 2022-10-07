<?php

namespace Karhal\Web3ConnectBundle\Model;

use Symfony\Component\Security\Core\User\UserInterface;

class Web3User implements UserInterface, Web3UserInterface
{
    public function getRoles(): array
    {
        // TODO: Implement getRoles() method.
    }

    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    public function getUserIdentifier(): string
    {
        // TODO: Implement getUserIdentifier() method.
    }


    public function getWalletAddress(): ?string
    {
        // TODO: Implement getWalletAddress() method.
    }

    public function setWalletAddress(string $wallet)
    {
        // TODO: Implement setWalletAddress() method.
    }
}
