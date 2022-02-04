<?php

namespace Karhal\Web3ConnectBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

class DataInitializedEvent extends Event
{
    public const NAME = 'web3user.data.initialized';

    private array $data = [];

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data)
    {
        $this->data = $data;
    }
}
