<?php

namespace SparkTests\Configuration;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spark\Configuration\DiactorosConfiguration;

class DiactorosConfigurationTest extends ConfigurationTestCase
{
    protected function getConfigurations()
    {
        return [
            new DiactorosConfiguration,
        ];
    }

    public function testApply()
    {
        $server_request = $this->injector->make(ServerRequestInterface::class);

        $this->assertInstanceOf('Zend\Diactoros\ServerRequest', $server_request);

        $request = $this->injector->make(RequestInterface::class);

        $this->assertSame($request, $server_request);

        $response = $this->injector->make(ResponseInterface::class);

        $this->assertInstanceOf('Zend\Diactoros\Response', $response);
    }
}
