<?php

namespace Spark\Handler;

use Aura\Payload_Interface\PayloadInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Spark\Adr\DomainInterface;
use Spark\Adr\InputInterface;
use Spark\Adr\ResponderInterface;
use Spark\Adr\RouteInterface;
use Spark\Resolver;

class ActionHandler
{
    /**
     * @var Resolver
     */
    protected $resolver;

    protected $routeAttribute = 'spark/adr:route';

    public function __construct(Resolver $resolver)
    {
        $this->resolver = $resolver;
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
        $resolver = $this->resolver;
        $domain    = $resolver($route->getDomain());
        $input     = $resolver($route->getInput());
        $responder = $resolver($route->getResponder());

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
