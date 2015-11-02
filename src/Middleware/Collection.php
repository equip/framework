<?php

namespace Spark\Middleware;

use Relay\MiddlewareInterface;

class Collection extends \ArrayObject
{
    /**
     * @param array $middlewares
     */
    public function __construct(array $middlewares)
    {
        $this->validate($middlewares);

        parent::__construct($middlewares);
    }

    /**
     * @param array $middlewares
     * @throws \DomainException if $middlewares does not conform to type expectations
     */
    protected function validate(array $middlewares)
    {
        foreach ($middlewares as $middleware) {
            if (!(is_callable($middleware) || is_subclass_of($middleware, MiddlewareInterface::class))) {
                throw new \DomainException(
                    'All elements of $middlewares must be callable or implement Relay\\MiddlewareInterface'
                );
            }
        }
    }
}
