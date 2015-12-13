<?php

namespace Spark\Configuration;

use Auryn\Injector;
use Relay\RelayBuilder;
use Spark\Middleware\Collection as Middleware;

class RelayConfiguration implements ConfigurationInterface
{
    /**
     * @inheritDoc
     */
    public function apply(Injector $injector)
    {
        $injector->define(
            RelayBuilder::class,
            [
                'resolver' => 'Relay\ResolverInterface',
            ]
        );

        $injector->delegate(
            'Relay\\Relay',
            function (RelayBuilder $builder, Middleware $queue) {
                return $builder->newInstance($queue);
            }
        );
    }
}
