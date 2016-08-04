<?php

namespace Equip\Configuration;

use Auryn\Injector;
use Equip\ContentNegotiation;
use Equip\Formatter\PlatesFormatter;

class PlatesFormatterConfiguration implements ConfigurationInterface
{
    public function apply(Injector $injector)
    {
        $injector->prepare(ContentNegotiation::class, function (ContentNegotiation $negotiator) {
            return $negotiator->withValue(PlatesFormatter::class, 1.0);
        });
    }
}
