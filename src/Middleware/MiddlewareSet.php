<?php

namespace Equip\Middleware;

use DomainException;
use Equip\Compatibility\StructureWithDataAlias;
use Equip\Structure\Set;

class MiddlewareSet extends Set
{
    use StructureWithDataAlias;

    /**
     * @inheritDoc
     *
     * @throws \DomainException if $middlewares does not conform to type expectations
     */
    protected function assertValid(array $middlewares)
    {
        parent::assertValid($middlewares);

        foreach ($middlewares as $middleware) {
            if (!(is_callable($middleware) || method_exists($middleware, '__invoke'))) {
                throw new DomainException(
                    'All elements of $middlewares must be callable'
                );
            }
        }
    }
}
