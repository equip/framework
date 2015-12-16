<?php

namespace Spark\Configuration;

use Auryn\Injector;
use Shadowhand\Destrukt\Set;
use Spark\Exception\ConfigurationException;

class ConfigurationSet extends Set implements ConfigurationInterface
{
    /**
     * @inheritDoc
     *
     * @throws ConfigurationException If any class is not of the expected type
     */
    public function validate(array $classes)
    {
        parent::validate($classes);

        foreach ($classes as $class) {
            if (!is_subclass_of($class, ConfigurationInterface::class)) {
                throw ConfigurationException::invalidClass($class);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function apply(Injector $injector)
    {
        foreach ($this as $class) {
            $configuration = $injector->make($class);
            $configuration->apply($injector);
        }
    }
}
