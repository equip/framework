<?php

namespace EquipTests\Configuration;

use Auryn\Injector;
use Equip\Configuration\MonologConfiguration;
use Monolog\Logger;
use PHPUnit_Framework_TestCase as TestCase;
use Psr\Log\LoggerInterface;

class MonologConfigurationTest extends TestCase
{
    public function testConfiguration()
    {
        if (!class_exists(Logger::class)) {
            $this->markTestSkipped('Monolog is not installed');
        }

        $injector = new Injector;
        $configuration = $injector->make(MonologConfiguration::class);
        $configuration->apply($injector);

        $logger = $injector->make(LoggerInterface::class);
        $this->assertInstanceOf(Logger::class, $logger);
        $this->assertSame($logger, $injector->make(LoggerInterface::class));
    }
}
