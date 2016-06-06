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
     * @throws ConfigurationException
     *  If $classes does not implement the correct interface.
     */
    protected function assertValid(array $classes)
    {
        parent::assertValid($classes);

        foreach ($classes as $config) {
            if (!is_subclass_of($config, ConfigurationInterface::class)) {
                throw ConfigurationException::invalidClass($config);
            }
        }
    }
}
