<?php

namespace Equip\Exception;

use DomainException;

class MiddlewareException extends DomainException
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
            'Middleware `%s` is not invokable',
            $spec
        ));
    }
}
