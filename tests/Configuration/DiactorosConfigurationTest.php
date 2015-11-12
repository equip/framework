<?php

namespace SparkTests\Configuration;

use Auryn\Injector;
use PHPUnit_Framework_TestCase as TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spark\Configuration\DiactorosConfiguration;

class DiactorosConfigurationTestCase extends TestCase
{
    public function testApply()
    {
        $injector = new Injector;

        $config = new DiactorosConfiguration;
        $config->apply($injector);

        $server_request = $injector->make(ServerRequestInterface::class);

        $this->assertInstanceOf('Zend\Diactoros\ServerRequest', $server_request);

        $request = $injector->make(RequestInterface::class);

        $this->assertSame($request, $server_request);

        $response = $injector->make(ResponseInterface::class);

        $this->assertInstanceOf('Zend\Diactoros\Response', $response);
    }
}
