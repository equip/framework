<?php

namespace EquipTests\Configuration;

use Equip\Configuration\DiactorosConfiguration;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;

class DiactorosConfigurationTest extends ConfigurationTestCase
{
    protected function getConfigurations()
    {
        return [
            new DiactorosConfiguration
        ];
    }

    public function dataMapping()
    {
        return [
            // https://github.com/relayphp/Relay.Relay/issues/25
            [RequestInterface::class, ServerRequest::class],
            [ResponseInterface::class, Response::class],
            [ServerRequestInterface::class, ServerRequest::class]
        ];
    }

    /**
     * @dataProvider dataMapping
     */
    public function testInstances($interface, $class)
    {
        $instance = $this->injector->make($interface);
        $this->assertInstanceOf($class, $instance);
    }
}
