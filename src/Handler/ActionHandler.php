<?php

namespace Spark\Handler;

use Arbiter\ActionHandler as Arbiter;
use Arbiter\Action;
use Spark\Adr\PayloadInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Spark\Adr\DomainInterface;
use Spark\Adr\InputInterface;
use Spark\Adr\ResponderInterface;
use Spark\Resolver\ResolverInterface;

class ActionHandler extends Arbiter
{
    /**
     * @var Spark\Resolver\ResolverInterface
     */
    protected $resolver;

    protected $actionAttribute = 'spark/adr:action';

    public function __construct(ResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface      $response,
        callable               $next
    ) {
        $action  = $request->getAttribute($this->actionAttribute);
        $request = $request->withoutAttribute($this->actionAttribute);

        $response = $this->getResponse($action, $request, $response);

        return $next($request, $response);
    }

    /**
     * Use the action collaborators to get a response.
     *
     * @param  Action                 $action
     * @param  ServerRequestInterface $request
     * @param  ResponseInterface     $response
     * @return ResponseInterface
     */
    private function getResponse(
        Action                 $action,
        ServerRequestInterface $request,
        ResponseInterface      $response
    ) {
        $domain    = $this->resolve($action->getDomain());
        $input     = $this->resolve($action->getInput());
        $responder = $this->resolve($action->getResponder());

        $payload  = $this->getPayload($domain, $input, $request);
        $response = $this->getResponseForPayload($responder, $request, $response, $payload);

        return $response;
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
    private function getResponseForPayload(
        ResponderInterface     $responder,
        ServerRequestInterface $request,
        ResponseInterface      $response,
        PayloadInterface       $payload
    ) {
        return $responder($request, $response, $payload);
    }
}
