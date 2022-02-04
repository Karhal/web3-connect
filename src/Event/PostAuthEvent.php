<?php

namespace Karhal\Web3ConnectBundle\Event;

use Karhal\Web3ConnectBundle\Model\Web3UserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\EventDispatcher\Event;

class PostAuthEvent extends Event
{
    public const NAME = 'web3user.loaded';

    private UserInterface $user;

    public function __construct(Web3UserInterface $user)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
    }
}
