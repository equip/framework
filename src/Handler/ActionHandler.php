<?php
namespace Spark\Handler;

use Aura\Payload_Interface\PayloadInterface;
use Auryn\Injector;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spark\Adr\DomainInterface;
use Spark\Adr\InputInterface;
use Spark\Adr\ResponderInterface;
use Spark\Adr\RouteInterface;
use Spark\Router;

class ActionHandler
{
    protected $injector;
    protected $router;

    public function __construct(Injector $injector, Router $router)
    {
        $this->injector = $injector;
        $this->router = $router;
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        RouteInterface $route
    ) {

        /**
         * @var $responder callable
         */
        $responder = $this->injector->make($route->getResponder() ?: $this->router->getResponder());
        $payload = null;
        if ($route->getDomain()) {
            $domain = $this->injector->make($route->getDomain());
            $input = $this->injector->make($route->getInput() ?: $this->router->getInput());
            $payload = $this->getPayload($domain, $input, $request);
        }
        return $this->getResponse($responder, $request, $response, $payload);
    }

    protected function getPayload(DomainInterface $domain, InputInterface $input, ServerRequestInterface $request)
    {
        return $domain($input($request));
    }

    protected function getResponse(
        ResponderInterface $responder,
        ServerRequestInterface $request,
        ResponseInterface $response,
        PayloadInterface $payload = null
    )
    {
        return $responder($request, $response, $payload);
    }
}