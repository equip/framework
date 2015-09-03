<?php

namespace Spark\Responder;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Spark\Adr\PayloadInterface;
use Spark\Adr\ResponderInterface;
use Spark\Resolver\ResolverInterface;

class ChainedResponder implements ResponderInterface
{
    /**
     * @var ResolverInterface
     */
    private $resolver;

    /**
     * @var array
     */
    private $responders = [
        'Spark\Responder\FormattedResponder',
    ];

    /**
     * @param ResolverInterface $resolver
     */
    public function __construct(
        ResolverInterface $resolver
    ) {
        $this->resolver = $resolver;
    }

    /**
     * Get a list of registered responders.
     *
     * @return array
     */
    public function getResponders()
    {
        return $this->responders;
    }

    /**
     * Get a copy with a new list of responders.
     *
     * @param  array $responders
     * @return self
     */
    public function withResponders(array $responders)
    {
        $new = clone $this;
        $new->responders = array_values($responders);
        return $new;
    }

    /**
     * Get a copy with an appended responder.
     *
     * @param  string $spec
     * @return self
     */
    public function withAddedResponder($spec)
    {
        $new = clone $this;
        $new->responders[] = $spec;
        return $new;
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface      $response,
        PayloadInterface       $payload
    ) {
        // Convert the responders from a list to a set
        $responders = array_unique($this->responders);

        // Create instances of all the responders via the resolver
        $responders = array_map($this->resolver, $responders);

        // Call each of the responders in FIFO order
        foreach ($responders as $responder) {
            $response = $responder($request, $response, $payload);
        }

        return $response;
    }
}
