<?php

namespace Spark\Configuration;

use Auryn\Injector;
use Spark\Exception\ConfigurationException;

class ConfigurationSet implements ConfigurationInterface
{
    /**
     * @var array
     */
    protected $classes = [];

    /**
     * @param array $classes
     */
    public function __construct(array $classes = [])
    {
        foreach ($classes as $class) {
            $this->add($class);
        }
    }

    /**
     * Add a new configuration class to the set
     *
     * @param string $class
     *
     * @return self
     */
    public function add($class)
    {
        $this->validate($class);

        $this->classes[] = $class;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function apply(Injector $injector)
    {
        foreach ($this->classes as $class) {
            $configuration = $injector->make($class);
            $configuration->apply($injector);
        }
    }

    /**
     * Checks that the given class is valid for configuration
     *
     * @param string $class
     *
     * @return void
     *
     * @throws ConfigurationException If the class is not of the expected type
     */
    protected function validate($class)
    {
        if (!is_subclass_of($class, ConfigurationInterface::class)) {
            throw ConfigurationException::invalidClass($class);
        }
    }
}
