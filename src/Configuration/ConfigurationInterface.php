<?php

namespace Equip\Configuration;

use Auryn\Injector;

interface ConfigurationInterface
{
    /**
     * Applies a configuration set to a dependency injector.
     *
     * @param Injector $injector
     */
    public function apply(Injector $injector);
}
