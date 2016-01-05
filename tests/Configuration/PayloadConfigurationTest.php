<?php

namespace EquipTests\Configuration;

use Equip\Adr\PayloadInterface;
use Equip\Configuration\PayloadConfiguration;
use Equip\Payload;

class PayloadConfigurationTest extends ConfigurationTestCase
{
    protected function getConfigurations()
    {
        return [
            new PayloadConfiguration,
        ];
    }

    public function testApply()
    {
        $payload = $this->injector->make(PayloadInterface::class);

        $this->assertInstanceOf(Payload::class, $payload);
    }
}
