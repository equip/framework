<?php

namespace SparkTests\Configuration;

use Auryn\Injector;
use Spark\Configuration\ConfigurationInterface;
use Spark\Configuration\ConfigurationSet;
use PHPUnit_Framework_TestCase as TestCase;

class ConfigurationSetTest extends TestCase
{
    public function testSet()
    {
        $config = $this->getMock(ConfigurationInterface::class);
        $injector = $this->getMock(Injector::class);

        $injector
            ->expects($this->once())
            ->method('make')
            ->with(get_class($config))
            ->willReturn($config);

        $config
            ->expects($this->once())
            ->method('apply')
            ->with($injector);

        $set = new ConfigurationSet([
            get_class($config),
        ]);

        $set->apply($injector);
    }

    /**
     * @expectedException Spark\Exception\ConfigurationException
     * @expectedExceptionRegExp /class .* must implement ConfigurationInterface/i
     */
    public function testInvalidClass()
    {
        $set = new ConfigurationSet;

        $set->add('\stdClass');
    }
}
