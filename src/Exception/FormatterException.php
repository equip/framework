<?php

namespace Equip\Exception;

use Equip\Formatter\FormatterInterface;
use InvalidArgumentException;

class FormatterException extends InvalidArgumentException
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
            'Formatter class `%s` must implement `%s`',
            $spec,
            FormatterInterface::class
        ));
    }

    /**
      * @param string|object $formatter
      *
      * @return static
      */
     public static function needsQuality($formatter)
     {
         if (is_object($formatter)) {
             $formatter = get_class($formatter);
         }

         return new static(sprintf(
             'No quality have been set for the `%s` formatter',
             $formatter
         ));
     }
}
