<?php

namespace Equip\Configuration;

use Auryn\Injector;
use Relay\RelayBuilder;
use Equip\Middleware\MiddlewareSet;

class RelayConfiguration implements ConfigurationInterface
{
    /**
     * @inheritDoc
     */
    public function apply(Injector $injector)
    {
        $injector->define(RelayBuilder::class, [
            'resolver' => 'Relay\ResolverInterface',
        ]);

        $factory = function (RelayBuilder $builder, MiddlewareSet $queue) {
            return $builder->newInstance($queue);
        };

        $injector->delegate('Relay\Relay', $factory);
    }
}
