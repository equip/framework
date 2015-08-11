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

    /**
     * Returns an instance of a specified class implementing __invoke() using
     * the underlying Auryn injector.
     *
     * @param string $spec Fully-qualified class name
     * @return callable Instance of the referenced class
     */
    public function __invoke($spec)
    {
        return $this->injector->make($spec);
    }
}
