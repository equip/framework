<?php

namespace Equip\Configuration;

use Auryn\Injector;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\ServerRequestFactory;

class DiactorosConfiguration implements ConfigurationInterface
{
    /**
     * @inheritDoc
     */
    public function apply(Injector $injector)
    {
        $injector->alias(
            RequestInterface::class,
            // It should not be necessary to force all requests to be server
            // requests, except that Relay uses the wrong type hint:
            // https://github.com/relayphp/Relay.Relay/issues/25
            //
            // 'Zend\Diactoros\Request'
            ServerRequest::class
        );

        $injector->alias(
            ResponseInterface::class,
            Response::class
        );

        $injector->alias(
            ServerRequestInterface::class,
            ServerRequest::class
        );

        $injector->delegate(
            ServerRequest::class,
            [ServerRequestFactory::class, 'fromGlobals']
        );
    }
}
