<?php

namespace Equip\Responder;

use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Equip\Adr\PayloadInterface;
use Equip\Adr\ResponderInterface;
use Equip\Resolver\ResolverTrait;
use Equip\Structure\Set;
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
        ResponseInterface      $response,
        PayloadInterface       $payload
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
     * @throws InvalidArgumentException If a responder does not implement the correct interface.
     */
    protected function assertValid(array $data)
    {
        parent::assertValid($data);

        foreach ($data as $responder) {
            if (!is_subclass_of($responder, ResponderInterface::class)) {
                throw new InvalidArgumentException(sprintf(
                    'All responders in `%s` must implement `%s`',
                    static::class,
                    ResponderInterface::class
                ));
            }
        }
    }
}
