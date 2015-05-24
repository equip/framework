<?php
namespace Spark\Handler;

use Auryn\Injector;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spark\Adr\RouteInterface;

class ActionHandler
{
    protected $injector;

    public function __construct(Injector $injector)
    {
        $this->injector = $injector;
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        RouteInterface $route
    ) {

        // TODO: Make this load a default responder
        /**
         * @var $responder callable
         */
        $responder = $this->injector->make($route->getResponder() ?: '\Spark\Responder\Responder');
        if ($route->getDomain()) {
            $payload = $this->domain($route, $request);
            return $responder($request, $response, $payload);
        }
        return $responder($request, $response);
    }

    protected function domain(RouteInterface $route, ServerRequestInterface $request)
    {
        /**
         * @var $domain callable
         */
        $domain = $this->injector->make($route->getDomain());
        if ($route->getInput()) {
            // TODO: Make a default input parser, and allow you to load a default
            /**
             * @var $input callable
             */
            $input = $this->injector->make($route->getInput());
            $input = (array) $input($request);
            return call_user_func_array($domain, $input);
        }
        return $domain();
    }
}