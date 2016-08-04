<?php

namespace Equip\Configuration;

use Auryn\Injector;
use Equip\Formatter\NegotiatedFormatter;
use Equip\Formatter\PlatesFormatter;

class PlatesFormatterConfiguration implements ConfigurationInterface
{
    public function apply(Injector $injector)
    {
        $injector->prepare(NegotiatedFormatter::class, function (NegotiatedFormatter $formatter) {
            return $formatter->withValue(PlatesFormatter::class, 1.0);
        });
    }
}
