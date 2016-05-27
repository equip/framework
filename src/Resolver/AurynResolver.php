<?php

namespace Equip\Resolver;

use Auryn\Injector;
use Relay\ResolverInterface;

class AurynResolver implements ResolverInterface
{
    /**
     * @var Injector
     */
    private $injector;

    public function __construct(Injector $injector)
    {
        $this->injector = $injector;
    }

    /**
     * Resolve a class spec into an object, if it is not already instantiated.
     *
     * @param string|object $specOrObject
     *
     * @return object
     */
    public function __invoke($specOrObject)
    {
        if (is_object($specOrObject)) {
            return $specOrObject;
        }

        return $this->injector->make($specOrObject);
    }
}
