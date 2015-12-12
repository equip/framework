<?php
namespace Spark\Resolver;

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
     * Get an instance of the given class
     *
     * @param string $spec Fully-qualified class name
     *
     * @return object
     */
    public function __invoke($spec)
    {
        return $this->injector->make($spec);
    }
}
