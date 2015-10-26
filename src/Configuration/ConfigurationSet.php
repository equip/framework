<?php

namespace Spark\Configuration;

use Auryn\Injector;

class ConfigurationSet implements ConfigurationInterface
{
    /**
     * @var array
     */
    protected $classes;

    /**
     * @param array $classes
     */
    public function __construct(array $classes)
    {
        $this->validateClasses($classes);

        $this->classes = $classes;
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
     * @param array $classes
     * @throws \DomainException if any classes cannot be loaded
     */
    protected function validateClasses(array $classes)
    {
        $invalid = array_filter(
            $classes,
            function ($class) {
                return !is_subclass_of($class, ConfigurationInterface::class);
            }
        );
        if ($invalid) {
            $message = 'Classes cannot be loaded or do not implement ConfigurationInterface: ' . implode(', ', $invalid);
            throw new \DomainException($message);
        }
    }
}
