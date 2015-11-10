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
            'Psr\Http\Message\ResponseInterface',
            'Zend\Diactoros\Response'
        );

        $injector->share('Psr\Http\Message\ResponseInterface');

        $injector->delegate(
            'Psr\Http\Message\ServerRequestInterface',
            'Zend\Diactoros\ServerRequestFactory::fromGlobals'
        );

        $injector->share('Psr\Http\Message\ServerRequestInterface');
    }
}
