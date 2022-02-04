<?php

namespace Karhal\Web3ConnectBundle\Model;

use Symfony\Component\Security\Core\User\UserInterface;

class Web3User implements UserInterface, Web3UserInterface
{

    public function getRoles()
    {
        // TODO: Implement getRoles() method.
    }

    public function getPassword()
    {
        // TODO: Implement getPassword() method.
    }

    public function getSalt()
    {
        // TODO: Implement getSalt() method.
    }

    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    public function getUsername()
    {
        // TODO: Implement getUsername() method.
    }

    public function __call($name, $arguments)
    {
        // TODO: Implement @method string getUserIdentifier()
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