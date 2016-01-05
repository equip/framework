<?php

namespace Equip\Exception;

use InvalidArgumentException;

class DirectoryException extends InvalidArgumentException
{
    /**
     * @param mixed $value
     *
     * @return static
     */
    public static function invalidEntry($value)
    {
        if (is_object($value)) {
            $value = get_class($value);
        }
        return new static(sprintf(
            'Directory entry `%s` is not an Action instance',
            $value
        ));
    }
}
