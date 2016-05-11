<?php

namespace Equip\Exception;

use DomainException;
use Equip\Configuration\ConfigurationInterface;

class ConfigurationException extends DomainException
{
    /**
     * @param string $spec
     *
     * @return static
     */
    public static function invalidClass($spec)
    {
        return new static(sprintf(
            'Configuration class `%s` must implement `%s`',
            $spec,
            ConfigurationInterface::class
        ));
    }
}
