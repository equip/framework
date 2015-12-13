<?php

namespace SparkTests\Configuration;

use Spark\Adr\PayloadInterface;
use Spark\Configuration\PayloadConfiguration;
use Spark\Payload;

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
