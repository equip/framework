<?php

namespace Spark\Exception;

use RuntimeException;

class HttpNotFound extends RuntimeException
{
    /**
     * @param string $path
     *
     * @return static
     */
    public static function invalidPath($path)
    {
        return new static(sprintf(
            'Cannot find any resource at `%s`',
            $path
        ));
    }

    /**
     * @return integer
     */
    public function getStatusCode()
    {
        return 404;
    }
}
