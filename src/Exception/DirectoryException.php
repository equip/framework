<?php

namespace Equip\Exception;

use Equip\Contract\ActionInterface;
use InvalidArgumentException;

class DirectoryException extends InvalidArgumentException
{
    /**
     * @param string|object $value
     *
     * @return static
     */
    public static function invalidEntry($value)
    {
        if (is_object($value)) {
            $value = get_class($value);
        }

        return new static(sprintf(
            'Directory entry `%s` must be an `%s` instance',
            $value,
            ActionInterface::class
        ));
    }
}
