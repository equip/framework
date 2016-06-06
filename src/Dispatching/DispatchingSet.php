<?php

namespace Equip\Dispatching;

use Auryn\Injector;
use Equip\Directory;
use Equip\Exception\DispatchingException;
use Equip\Structure\Set;

class DispatchingSet extends Set
{
    /**
     * Handle directory preparation for injection.
     *
     * Applies each of the dispatchers in the current set to the directory.
     *
     * @param Directory $directory
     * @param Injector $injector
     *
     * @return Directory
     */
    public function __invoke(Directory $directory, Injector $injector)
    {
        foreach ($this as $dispatch) {
            if (!is_callable($dispatch)) {
                $dispatch = $injector->make($dispatch);
            }
            $directory = $dispatch($directory);
        }

        return $directory;
    }

    /**
     * @inheritDoc
     *
     * @throws DispatchingException
     *  If $classes does not conform to type expectations.
     */
    protected function assertValid(array $classes)
    {
        parent::assertValid($classes);

        foreach ($classes as $dispatching) {
            if (!(is_callable($dispatching) || method_exists($dispatching, '__invoke'))) {
                throw DispatchingException::notInvokable($dispatching);
            }
        }
    }
}
