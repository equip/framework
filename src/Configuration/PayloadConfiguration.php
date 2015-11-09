<?php

namespace Spark\Configuration;

use Auryn\Injector;

class PayloadConfiguration implements ConfigurationInterface
{
    /**
     * @inheritDoc
     */
    public function apply(Injector $injector)
    {
        $injector->alias(
            'Spark\Adr\PayloadInterface',
            'Spark\Payload'
        );
    }
}

