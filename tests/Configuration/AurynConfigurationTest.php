<?php

namespace EquipTests\Configuration;

use Auryn\Injector;
use Equip\Configuration\AurynConfiguration;
use Equip\Resolver\AurynResolver;
use Relay\ResolverInterface;

class AurynConfigurationTest extends ConfigurationTestCase
{
    protected function getConfigurations()
    {
        return [
            new AurynConfiguration,
        ];
    }

    public function testApply()
    {
        $resolver = $this->injector->make(ResolverInterface::class);
        $this->assertInstanceOf(AurynResolver::class, $resolver);

        // Injector is not a singleton
        $injector = $this->injector->make(Injector::class);
        $this->assertNotSame($injector, $this->injector);
    }
}
