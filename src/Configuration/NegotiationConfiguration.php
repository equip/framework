<?php

namespace Spark\Configuration;

use Auryn\Injector;

class NegotiationConfiguration implements ConfigurationInterface
{
    /**
     * @inheritDoc
     */
    public function apply(Injector $injector)
    {
        $injector->alias(
            'Negotiation\NegotiatorInterface',
            'Negotiation\Negotiator'
        );
    }
}
