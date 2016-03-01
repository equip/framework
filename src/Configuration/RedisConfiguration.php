<?php

namespace Equip\Configuration;

use Auryn\Injector;
use Redis;

class RedisConfiguration implements ConfigurationInterface
{
    use EnvTrait;

    /**
     * @inheritDoc
     */
    public function apply(Injector $injector)
    {
        $injector->prepare(Redis::class, function($redis) {
            $redis->connect(
                $this->env->getValue('REDIS_HOST', '127.0.0.1'),
                $this->env->getValue('REDIS_PORT', 6379)
            );
        });
    }
}
