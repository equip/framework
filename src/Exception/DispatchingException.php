<?php

namespace Equip\Exception;

use InvalidArgumentException;

class DispatchingException extends InvalidArgumentException
{
    /**
     * @param string|object $spec
     *
     * @return static
     */
    public static function notInvokable($spec)
    {
        if (is_object($spec)) {
            $spec = get_class($spec);
        }

        return new static(sprintf(
            'Dispatcher `%s` is not invokable',
            $spec
        ));
    }
}
