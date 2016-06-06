<?php

namespace Equip\Exception;

use Equip\Formatter\FormatterInterface;
use InvalidArgumentException;

class FormatterException extends InvalidArgumentException
{
    /**
     * @param string $spec
     *
     * @return static
     */
    public static function invalidClass($spec)
    {
        return new static(sprintf(
            'Formatter class `%s` must implement `%s`',
            $spec,
            FormatterInterface::class
        ));
    }

    /**
      * @param string $formatter
      *
      * @return static
      */
     public static function needsQuality($formatter)
     {
         return new static(sprintf(
             'No quality have been set for the `%s` formatter',
             $formatter
         ));
     }
}
