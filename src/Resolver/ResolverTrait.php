<?php

namespace Equip\Resolver;

trait ResolverTrait
{
    /**
     * @var \Relay\ResolverInterface
     */
    private $resolver;

    /**
     * Resolve a class spec into an object.
     *
     * @param string $spec Fully-qualified class name
     *
     * @return object
     */
    private function resolve($spec)
    {
        return call_user_func($this->resolver, $spec);
    }
}
