<?php

namespace Spark\Exception;

use InvalidArgumentException;

class EnvException extends InvalidArgumentException
{
    /**
     * @param string $path
     *
     * @return static
     */
    public static function invalidFile($path)
    {
        return new static(sprintf(
            'Environment file `%s` does not exist or is not readable',
            $path
        ));
    }

    /**
     * @return static
     */
    public static function detectionFailed()
    {
        return new static(
            'Unable to automatically detect the location of a .env file'
        );
    }
}
