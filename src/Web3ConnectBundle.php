<?php

namespace Karhal\Web3ConnectBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class Web3ConnectBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
