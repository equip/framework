<?php

namespace Equip\Configuration;

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
            // It should not be necessary to force all requests to be server
            // requests, except that Relay uses the wrong type hint:
            // https://github.com/relayphp/Relay.Relay/issues/25
            //
            // 'Zend\Diactoros\Request'
            'Zend\Diactoros\ServerRequest'
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
