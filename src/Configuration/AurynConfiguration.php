<?php

namespace Equip\Configuration;

use Auryn\Injector;
use Equip\Resolver\AurynResolver;
use Relay\ResolverInterface;

class AurynConfiguration implements ConfigurationInterface
{
    /**
     * @inheritDoc
     */
    public function apply(Injector $injector)
    {
        $injector->alias(
            ResolverInterface::class,
            AurynResolver::class
        );

        $injector->define(AurynResolver::class, [
            ':injector' => $injector,
        ]);
    }
}
