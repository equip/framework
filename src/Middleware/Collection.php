<?php

namespace Spark\Middleware;

use Relay\MiddlewareInterface;

class Collection extends \ArrayObject
{
    /**
     * @param array $middlewares
     */
    public function __construct(array $middlewares = [])
    {
        $this->validate($middlewares);
        parent::__construct($middlewares);
    }

    /**
     * Add middleware to the collection
     *
     * By default will append the middleware to the end of the stack. Optionally
     * can insert the middleware in the stack before an existing one.
     *
     * @param string $class
     * @param mixed $before
     *
     * @return static
     */
    public function withAddedMiddleware($class, $before = null)
    {
        $this->validate([$class]);

        $middleware = $this->getArrayCopy();

        $offset = array_search($before, $middleware);
        if ($offset === false) {
            $offset = count($middleware);
        }

        array_splice($middleware, $offset, 0, $class);

        $copy = clone $this;
        $copy->exchangeArray($middleware);

        return $copy;
    }

    /**
     * @param array $middlewares
     * @throws \DomainException if $middlewares does not conform to type expectations
     */
    protected function validate(array $middlewares)
    {
        foreach ($middlewares as $middleware) {
            if (!(is_callable($middleware) || method_exists($middleware, '__invoke'))) {
                throw new \DomainException(
                    'All elements of $middlewares must be callable or implement __invoke()'
                );
            }
        }
    }
}
