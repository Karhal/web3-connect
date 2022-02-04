<?php

namespace Karhal\Web3ConnectBundle\Model;

interface Web3UserInterface
{
    public function getWalletAddress(): ?string;

    public function setWalletAddress(string $wallet);
}
