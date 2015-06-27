<?php
namespace Spark;

use Auryn\Injector;

class Resolver
{
    protected $injector;

    public function __construct(Injector $injector)
    {
        $this->injector = $injector;
    }

    public function __invoke($spec)
    {
        if (is_callable($spec)) {
            return $spec;
        }
        return $this->injector->make($spec);
    }
}