<?php

namespace Equip\Responder;

use Equip\Adr\PayloadInterface;
use Equip\Adr\ResponderInterface;
use Equip\Exception\ResponderException;
use Equip\Resolver\ResolverTrait;
use Equip\Structure\Set;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Relay\ResolverInterface;

class ChainedResponder extends Set implements ResponderInterface
{
    use ResolverTrait;

    /**
     * @param ResolverInterface $resolver
     * @param array $responders
     */
    public function __construct(
        ResolverInterface $resolver,
        array $responders = [
            FormattedResponder::class,
            RedirectResponder::class,
            StatusResponder::class,
        ]
    ) {
        $this->resolver = $resolver;

        return parent::__construct($responders);
    }

    /**
     * @inheritDoc
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        PayloadInterface $payload
    ) {
        foreach ($this as $responder) {
            $responder = $this->resolve($responder);
            $response = $responder($request, $response, $payload);
        }

        return $response;
    }

    /**
     * @inheritDoc
     *
     * @throws ResponderException
     *  If $classes does not implement the correct interface.
     */
    protected function assertValid(array $classes)
    {
        parent::assertValid($classes);

        foreach ($classes as $responder) {
            if (!is_subclass_of($responder, ResponderInterface::class)) {
                throw ResponderException::invalidClass($responder);
            }
        }
    }
}
