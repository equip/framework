<?php
namespace Spark\Resolver;

use Auryn\Injector;

class AurynResolver implements ResolverInterface
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
