<?php

namespace SparkTests\Configuration;

use Auryn\Injector;
use PHPUnit_Framework_TestCase as TestCase;
use Spark\Configuration\DefaultConfigurationSet;

class DefaultConfigurationTest extends TestCase
{
    public function testDefault()
    {
        $injector = $this->prophesize(Injector::class);

        $expected = [
            'Spark\Configuration\AurynConfiguration',
            'Spark\Configuration\DiactorosConfiguration',
            'Spark\Configuration\NegotiationConfiguration',
            'Spark\Configuration\PayloadConfiguration',
            'Spark\Configuration\RelayConfiguration',
        ];

        foreach ($expected as $class) {
            // We have to mock the interaction of the configuration,
            // because the values of the configuration are not public.
            // This is probably something we should refactor in the future...
            // maybe use Shadowhand\Destrukt\Set as the base?
            $mock = $this->prophesize($class);
            $injector->make($class)->willReturn($mock->reveal());
            $mock->apply($injector->reveal())->shouldBeCalled();
        }

        $set = new DefaultConfigurationSet;
        $set->apply($injector->reveal());
    }
}
