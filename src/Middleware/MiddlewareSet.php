<?php

namespace Equip\Middleware;

use Equip\Exception\MiddlewareException;
use Equip\Structure\Set;

class MiddlewareSet extends Set
{
    /**
     * @inheritDoc
     *
     * @throws MiddlewareException
     * If $classes does not conform to type expectations.
     */
    protected function assertValid(array $classes)
    {
        parent::assertValid($classes);

        foreach ($classes as $middleware) {
            if (!(is_callable($middleware) || method_exists($middleware, '__invoke'))) {
                throw MiddlewareException::notInvokable($middleware);
            }
        }
    }
}
