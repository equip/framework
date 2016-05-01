<?php

namespace Equip\Configuration;

use Auryn\Injector;
use Equip\Exception\ConfigurationException;
use Equip\Structure\Set;

class ConfigurationSet extends Set implements ConfigurationInterface
{
    /**
     * @inheritDoc
     */
    public function apply(Injector $injector)
    {
        foreach ($this as $configuration) {
            if (!is_object($configuration)) {
                $configuration = $injector->make($configuration);
            }
            $configuration->apply($injector);
        }
    }

    /**
     * @inheritDoc
     *
     * @throws ConfigurationException If any class is not of the expected type
     */
    protected function assertValid(array $classes)
    {
        parent::assertValid($classes);

        foreach ($classes as $class) {
            if (!is_subclass_of($class, ConfigurationInterface::class)) {
                throw ConfigurationException::invalidClass($class);
            }
        }
    }
}
