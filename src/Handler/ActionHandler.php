<?php

namespace Equip\Handler;

use Equip\Action;
use Equip\Adr\DomainInterface;
use Equip\Adr\InputInterface;
use Equip\Adr\PayloadInterface;
use Equip\Adr\ResponderInterface;
use Equip\Resolver\ResolverTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Relay\ResolverInterface;

class ActionHandler
{
    use ResolverTrait;

    const ACTION_ATTRIBUTE = 'equip/adr:action';

    /**
     * @param ResolverInterface $resolver
     */
    public function __construct(ResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable $next
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next
    ) {
        $action = $request->getAttribute(self::ACTION_ATTRIBUTE);
        $request = $request->withoutAttribute(self::ACTION_ATTRIBUTE);

        $response = $this->handle($action, $request, $response);

        return $next($request, $response);
    }

    /**
     * Use the action collaborators to get a response.
     *
     * @param Action $action
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     *
     * @return ResponseInterface
     */
    private function handle(
        Action $action,
        ServerRequestInterface $request,
        ResponseInterface $response
    ) {
        $domain = $this->resolve($action->getDomain());
        $input = $this->resolve($action->getInput());
        $responder = $this->resolve($action->getResponder());

        $payload = $this->payload($domain, $input, $request);
        $response = $this->response($responder, $request, $response, $payload);

        return $response;
    }

    /**
     * Execute the domain to get a payload using input from the request.
     *
     * @param DomainInterface $domain
     * @param InputInterface $input
     * @param ServerRequestInterface $request
     *
     * @return PayloadInterface
     */
    private function payload(
        DomainInterface $domain,
        InputInterface $input,
        ServerRequestInterface $request
    ) {
        return $domain($input($request));
    }

    /**
     * Execute the responder to marshall the payload into the response.
     *
     * @param ResponderInterface $responder
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param PayloadInterface $payload
     *
     * @return ResponseInterface
     */
    private function response(
        ResponderInterface $responder,
        ServerRequestInterface $request,
        ResponseInterface $response,
        PayloadInterface $payload
    ) {
        return $responder($request, $response, $payload);
    }
}
