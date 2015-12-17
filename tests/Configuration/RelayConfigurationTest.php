<?php
namespace SparkTests\Configuration;

use Relay\MiddlewareInterface;
use Relay\Relay;
use Spark\Configuration\AurynConfiguration;
use Spark\Configuration\RelayConfiguration;
use Spark\Middleware\Collection as MiddlewareCollection;

class RelayConfigurationTest extends ConfigurationTestCase
{
    protected function getConfigurations()
    {
        return [
            new AurynConfiguration,
            new RelayConfiguration,
        ];
    }

    public function testApply()
    {
        $dispatcher = $this->injector->make(Relay::class);

        $this->assertInstanceOf(Relay::class, $dispatcher);
    }
}
