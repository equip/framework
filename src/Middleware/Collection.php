<?php

namespace Spark\Middleware;

use Relay\MiddlewareInterface;

class Collection extends \ArrayObject
{
    /**
     * @param array $middlewares
     */
    public function __construct($middlewares)
    {
        $this->validate($middlewares);

        parent::__construct($middlewares);
    }

    /**
     * @param array $middlewares
     * @throws \DomainException if $middlewares does not conform to type expectations
     */
    protected function validate($middlewares)
    {
        if (!is_array($middlewares)) {
            throw new \DomainException('$middlewares must be an array');
        }

        foreach ($middlewares as $middleware) {
            if (!(is_callable($middleware) || $middleware instanceof MiddlewareInterface)) {
                throw new \DomainException(
                    'All elements of $middlewares must be callable or implement Relay\\MiddlewareInterface'
                );
            }
        }
    }
}
