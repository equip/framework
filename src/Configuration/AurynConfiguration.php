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
        $injector->alias(
            'Relay\ResolverInterface',
            'Spark\Resolver\AurynResolver'
        );

        $injector->define('Spark\Resolver\AurynResolver', [
            ':injector' => $injector,
        ]);
    }
}
