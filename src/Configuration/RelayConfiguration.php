<?php

namespace Equip\Configuration;

use Auryn\Injector;
use Equip\Middleware\MiddlewareSet;
use Relay\Relay;
use Relay\RelayBuilder;
use Relay\ResolverInterface;

class RelayConfiguration implements ConfigurationInterface
{
    /**
     * @inheritDoc
     */
    public function apply(Injector $injector)
    {
        $injector->define(RelayBuilder::class, [
            'resolver' => ResolverInterface::class
        ]);

        $factory = function (RelayBuilder $builder, MiddlewareSet $queue) {
            return $builder->newInstance($queue);
        };

        $injector->delegate(Relay::class, $factory);
    }
}
