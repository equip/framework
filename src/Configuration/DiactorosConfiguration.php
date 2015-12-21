<?php

namespace Spark\Configuration;

use Auryn\Injector;

class DiactorosConfiguration implements ConfigurationInterface
{
    /**
     * @inheritDoc
     */
    public function apply(Injector $injector)
    {
        $injector->alias(
            'Psr\Http\Message\RequestInterface',
            'Zend\Diactoros\Request'
        );

        $injector->alias(
            'Psr\Http\Message\ResponseInterface',
            'Zend\Diactoros\Response'
        );

        $injector->alias(
            'Psr\Http\Message\ServerRequestInterface',
            'Zend\Diactoros\ServerRequest'
        );

        $injector->delegate(
            'Zend\Diactoros\ServerRequest',
            'Zend\Diactoros\ServerRequestFactory::fromGlobals'
        );
    }
}
