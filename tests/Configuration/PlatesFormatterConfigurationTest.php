<?php

namespace EquipTests\Configuration;

use Equip\Configuration\AurynConfiguration;
use Equip\Configuration\PlatesFormatterConfiguration;
use Equip\Formatter\PlatesFormatter;
use Equip\Formatter\NegotiatedFormatter;
use League\Plates\Engine;

class PlatesFormatterConfigurationTest extends ConfigurationTestCase
{
    protected function getConfigurations()
    {
        if (!class_exists(Engine::class)) {
            $this->markTestSkipped('Plates is not installed');
        }

        return [
            new AurynConfiguration,
            new PlatesFormatterConfiguration,
        ];
    }

    public function testApply()
    {
        $formatter = $this->injector->make(NegotiatedFormatter::class);

        $this->assertArrayHasKey(PlatesFormatter::class, $formatter);
        $this->assertSame(1.0, $formatter[PlatesFormatter::class]);
    }
}
