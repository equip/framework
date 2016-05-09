<?php

namespace Equip\Configuration;

use Auryn\Injector;
use Equip\Adr\PayloadInterface;
use Equip\Payload;

class PayloadConfiguration implements ConfigurationInterface
{
    /**
     * @inheritDoc
     */
    public function apply(Injector $injector)
    {
        $injector->alias(
            PayloadInterface::class,
            Payload::class
        );
    }
}

