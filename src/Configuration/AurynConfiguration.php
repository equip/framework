<?php

namespace Spark\Configuration;

use Auryn\Injector;

class AurynConfiguration implements ConfigurationInterface
{
    /**
     * @inheritDoc
     */
    public function apply(Injector $injector)
    {
        $injector->share($injector);

        $injector->alias(
            'Spark\Resolver\ResolverInterface',
            'Spark\Resolver\AurynResolver'
        );
    }
}
