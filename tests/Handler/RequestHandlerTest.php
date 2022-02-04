<?php

namespace Karhal\Web3ConnectBundle\Tests\Handler;

use Karhal\Web3ConnectBundle\Handler\RequestHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class RequestHandlerTest extends TestCase
{
    public function testReplaceJsonDataFromRequest()
    {
        $json = \json_encode(['foo' => 'bar']);
        $request = new Request([], [], [], [], [], [], $json);
        $request->headers->set('Content-Type', 'application/json');
        RequestHandler::replaceJsonDataFromRequest($request);
        $this->assertEquals($request->request->get('foo'), 'bar');
    }
}