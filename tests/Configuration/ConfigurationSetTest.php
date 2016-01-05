<?php

namespace EquipTests\Configuration;

use Auryn\Injector;
use Equip\Configuration\ConfigurationInterface;
use Equip\Configuration\ConfigurationSet;
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

    public function testSetObject()
    {
        $config = $this->getMock(ConfigurationInterface::class);
        $injector = $this->getMock(Injector::class);

        $config
            ->expects($this->once())
            ->method('apply')
            ->with($injector);

        $set = new ConfigurationSet([
            $config,
        ]);

        $set->apply($injector);
    }

    /**
     * @expectedException Equip\Exception\ConfigurationException
     * @expectedExceptionRegExp /class .* must implement ConfigurationInterface/i
     */
    public function testInvalidClass()
    {
        $set = new ConfigurationSet;
        $set = $set->withValue('\stdClass');
    }
}
