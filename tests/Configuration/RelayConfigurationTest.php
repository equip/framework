<?php
namespace SparkTests\Configuration;

use Auryn\Injector;
use PHPUnit_Framework_TestCase as TestCase;
use Relay\MiddlewareInterface;
use Relay\Relay;
use Spark\Configuration\AurynConfiguration;
use Spark\Configuration\RelayConfiguration;
use Spark\Middleware\Collection as MiddlewareCollection;

class RelayConfigurationTestCase extends TestCase
{
    public function testApply()
    {
        $injector = new Injector;

        $auryn = new AurynConfiguration;
        $auryn->apply($injector);

        $relay = new RelayConfiguration;
        $relay->apply($injector);

        $middleware = $this->getMockBuilder(MiddlewareInterface::class)->getMock();
        $injector->define(MiddlewareCollection::class, [':middlewares' => [$middleware]]);

        $dispatcher = $injector->make(Relay::class);

        $this->assertInstanceOf(Relay::class, $dispatcher);
    }
}
