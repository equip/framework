<?php

namespace EquipTests\Configuration;

use Equip\Configuration\AurynConfiguration;
use Equip\Configuration\PlatesFormatterConfiguration;
use Equip\ContentNegotiation;
use Equip\Formatter\PlatesFormatter;
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
        $negotiator = $this->injector->make(ContentNegotiation::class);

        $this->assertArrayHasKey(PlatesFormatter::class, $negotiator);
        $this->assertSame(1.0, $negotiator[PlatesFormatter::class]);
    }
}
