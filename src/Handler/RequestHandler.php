<?php

namespace Karhal\Web3ConnectBundle\Handler;

use Symfony\Component\HttpFoundation\Request;

abstract class RequestHandler
{
    public static function replaceJsonDataFromRequest(Request $request)
    {
        $data = \json_decode((string)$request->getContent(), true, 512, \JSON_THROW_ON_ERROR);

        if (\is_array($data)) {
            $request->request->replace($data);
        }
    }
}
