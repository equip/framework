<?php

namespace SparkTests\Configuration;

use Spark\Configuration\AurynConfiguration;
use Spark\Configuration\PlatesResponderConfiguration;
use Spark\Formatter\PlatesFormatter;
use Spark\Responder\FormattedResponder;

class PlatesResponderConfigurationTest extends ConfigurationTestCase
{
    protected function getConfigurations()
    {
        if (!class_exists('League\Plates\Engine')) {
            $this->markTestSkipped('Plates is not installed');
        }

        return [
            new AurynConfiguration,
            new PlatesResponderConfiguration,
        ];
    }

    public function testApply()
    {
        $responder = $this->injector->make(FormattedResponder::class);

        $this->assertArrayHasKey(PlatesFormatter::class, $responder);
        $this->assertSame(1.0, $responder[PlatesFormatter::class]);
    }
}
