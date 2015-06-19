<?php

namespace Spark\Handler;

use Aura\Payload_Interface\PayloadInterface;
use Auryn\Injector;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Spark\Adr\DomainInterface;
use Spark\Adr\InputInterface;
use Spark\Adr\ResponderInterface;
use Spark\Adr\RouteInterface;

class ActionHandler
{
    protected $injector;

    protected $routeAttribute = 'spark/adr:route';

    public function __construct(Injector $injector)
    {
        $this->injector = $injector;
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface      $response,
        callable               $next
    ) {
        /**
         * @var RouteInterface
         */
        $route     = $request->getAttribute($this->routeAttribute);

        if (!($route instanceof RouteInterface)) {
            throw new \Exception(sprintf('"%s" request attribute does not implement RouteInterface', $this->routeAttribute));
        }

        // Resolve using the injector
        $domain    = $this->injector->make($route->getDomain());
        $input     = $this->injector->make($route->getInput());
        $responder = $this->injector->make($route->getResponder());

        $payload  = $this->getPayload($domain, $input, $request);
        $response = $this->getResponse($responder, $request, $response, $payload);

        return $next($request, $response);
    }

    /**
     * Execute the domain to get a payload.
     *
     * @param  DomainInterface        $domain
     * @param  InputInterface         $input
     * @param  ServerRequestInterface $request
     * @return PayloadInterface
     */
    private function getPayload(
        DomainInterface        $domain,
        InputInterface         $input,
        ServerRequestInterface $request
    ) {
        return $domain($input($request));
    }

    /**
     * Execute the responder to marshall the reponse.
     *
     * @param  ResponderInterface     $responder
     * @param  ServerRequestInterface $request
     * @param  ResponseInterface      $response
     * @param  PayloadInterface       $payload
     * @return ResponseInterface
     */
    private function getResponse(
        ResponderInterface     $responder,
        ServerRequestInterface $request,
        ResponseInterface      $response,
        PayloadInterface       $payload
    ) {
        return $responder($request, $response, $payload);
    }
}
