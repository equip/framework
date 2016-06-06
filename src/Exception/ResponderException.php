<?php

namespace Equip\Exception;

use Equip\Adr\ResponderInterface;
use InvalidArgumentException;

class ResponderException extends InvalidArgumentException
{
    /**
     * @param string $spec
     *
     * @return static
     */
    public static function invalidClass($spec)
    {
        return new static(sprintf(
            'Responder class `%s` must implement `%s`',
            $spec,
            ResponderInterface::class
        ));
    }
}
