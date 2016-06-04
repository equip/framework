<?php

namespace Equip\Exception;

use Equip\Adr\ResponderInterface;
use InvalidArgumentException;

class ResponderException extends InvalidArgumentException
{
    /**
     * @param string|object $spec
     *
     * @return static
     */
    public static function invalidClass($spec)
    {
        if (is_object($spec)) {
            $spec = get_class($spec);
        }

        return new static(sprintf(
            'Responder class `%s` must implement `%s`',
            $spec,
            ResponderInterface::class
        ));
    }
}
