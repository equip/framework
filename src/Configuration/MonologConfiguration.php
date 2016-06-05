<?php

namespace Equip\Configuration;

use Auryn\Injector;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

class MonologConfiguration implements ConfigurationInterface
{
    use EnvTrait;

    /**
     * @inheritDoc
     */
    public function apply(Injector $injector)
    {
        $injector->alias(
            LoggerInterface::class,
            Logger::class
        );

        $injector->share(Logger::class);

        $injector->define(Logger::class, [
            ':name' => $this->env->getValue('LOGGER_NAME', 'equip')
        ]);
    }
}
