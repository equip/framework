<?php

namespace EquipTests\Configuration;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Equip\Configuration\DiactorosConfiguration;

class DiactorosConfigurationTest extends ConfigurationTestCase
{
    protected function getConfigurations()
    {
        return [
            new DiactorosConfiguration,
        ];
    }

    public function dataMapping()
    {
        return [
            // https://github.com/relayphp/Relay.Relay/issues/25
            [RequestInterface::class, 'Zend\Diactoros\ServerRequest'],
            [ResponseInterface::class, 'Zend\Diactoros\Response'],
            [ServerRequestInterface::class, 'Zend\Diactoros\ServerRequest'],
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
