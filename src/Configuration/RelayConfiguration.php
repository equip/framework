<?php

namespace Spark\Configuration;

use Auryn\Injector;

class RelayConfiguration implements ConfigurationInterface
{
    /**
     * @inheritDoc
     */
    public function apply(Injector $injector)
    {
        $injector->alias(
            'Relay',
            'Relay\RelayBuilder'
        );
    }
}
