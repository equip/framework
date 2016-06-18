<?php

namespace EquipTests\Configuration;

use Auryn\Injector;
use Equip\Configuration\RedisConfiguration;
use Equip\Env;
use PHPUnit_Framework_TestCase as TestCase;
use Redis;

class RedisConfigurationTest extends TestCase
{
    public function testApply()
    {
        if (!class_exists(Redis::class)) {
            $this->markTestSkipped('Redis is not installed');
        }

        $injector = new Injector;
        $injector->delegate(Redis::class, function() {
            $redisMock = $this->createMock(Redis::class, ['connect']);

            $redisMock
                ->expects($this->once())
                ->method('connect');

            return $redisMock;
        });

        $config = new RedisConfiguration(
            $this->createMock(Env::class)
        );
        $config->apply($injector);

        $redis = $injector->make(Redis::class);
        $this->assertInstanceOf(Redis::class, $redis);
    }
}
