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

    public function dataMapping()
    {
        return [
            [RequestInterface::class, 'Zend\Diactoros\Request'],
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
