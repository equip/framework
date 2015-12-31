<?php

namespace Spark\Resolver;

trait ResolverTrait
{
    /**
     * @var \Relay\ResolverInterface
     */
    private $resolver;

    /**
     * Resolve a class spec into an object, if it is not already instantiated.
     *
     * @param string|object $specOrObject
     *
     * @return object
     */
    private function resolve($specOrObject)
    {
        if (is_object($specOrObject)) {
            return $specOrObject;
        }

        return call_user_func($this->resolver, $specOrObject);
    }
}
