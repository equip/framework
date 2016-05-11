<?php

namespace Equip\Exception;

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
        $messages = [];
        $filepath = realpath($path);

        if (!$filepath) {
            $messages[] = 'does not exist';
        }

        if ($filepath && !is_file($filepath)) {
            $messages[] = 'exists and is not a file';
        }

        if ($filepath && !is_readable($filepath)) {
            $messages[] = 'is not readable';
        }

        return new self(sprintf('Environment file `%s`: %s', $path, implode(', ', $messages)));
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
