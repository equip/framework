<?php

namespace Equip\Configuration;

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
            'Equip\Resolver\AurynResolver'
        );

        $injector->define('Equip\Resolver\AurynResolver', [
            ':injector' => $injector,
        ]);
    }
}
