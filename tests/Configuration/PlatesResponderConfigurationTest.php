<?php

namespace SparkTests\Configuration;

use Spark\Configuration\AurynConfiguration;
use Spark\Configuration\NegotiationConfiguration;
use Spark\Configuration\PlatesResponderConfiguration;
use Spark\Formatter\PlatesFormatter;
use Spark\Responder\FormattedResponder;

class PlatesResponderConfigurationTest extends ConfigurationTestCase
{
    protected function getConfigurations()
    {
        return [
            new AurynConfiguration,
            new NegotiationConfiguration,
            new PlatesResponderConfiguration,
        ];
    }

    public function testApply()
    {
        $responder = $this->injector->make(FormattedResponder::class);
        $formatters = $responder->getFormatters();

        $this->assertArrayHasKey(PlatesFormatter::class, $formatters);
        $this->assertSame(1.0, $formatters[PlatesFormatter::class]);
    }
}
