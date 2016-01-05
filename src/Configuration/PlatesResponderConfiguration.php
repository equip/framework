<?php

namespace Equip\Configuration;

use Auryn\Injector;
use Equip\Formatter\PlatesFormatter;
use Equip\Responder\FormattedResponder;

class PlatesResponderConfiguration implements ConfigurationInterface
{
    public function apply(Injector $injector)
    {
        $injector->prepare(FormattedResponder::class, function (FormattedResponder $responder) {
            return $responder->withValue(PlatesFormatter::class, 1.0);
        });
    }
}
