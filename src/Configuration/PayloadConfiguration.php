<?php

namespace Equip\Configuration;

use Auryn\Injector;

class PayloadConfiguration implements ConfigurationInterface
{
    /**
     * @inheritDoc
     */
    public function apply(Injector $injector)
    {
        $injector->alias(
            'Equip\Adr\PayloadInterface',
            'Equip\Payload'
        );
    }
}

