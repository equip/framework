<?php

namespace Spark\Exception;

use DomainException;

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
            'Configuration class `%s` must implement ConfigurationInterface',
            $spec
        ));
    }
}
