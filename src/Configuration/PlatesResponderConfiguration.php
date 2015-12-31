<?php

namespace Spark\Configuration;

use Auryn\Injector;
use Spark\Formatter\PlatesFormatter;
use Spark\Responder\FormattedResponder;

class PlatesResponderConfiguration implements ConfigurationInterface
{
    public function apply(Injector $injector)
    {
        $injector->prepare(FormattedResponder::class, function (FormattedResponder $responder) {
            return $responder->withValue(PlatesFormatter::class, 1.0);
        });
    }
}
