<?php
namespace Equip\Formatter;

use Equip\Adr\PayloadInterface;

abstract class AbstractFormatter
{
    /**
     * Get the content types this formatter can satisfy.
     *
     * @return array
     */
    public static function accepts()
    {
        throw new \RuntimeException(sprintf(
            '%s::%s() must be defined to declare accepted content types',
            static::class,
            __FUNCTION__
        ));
    }

    /**
     * Get the content type of the response body.
     *
     * @return string
     */
    abstract protected function type();

    /**
     * Get the response body from the payload.
     *
     * @param PayloadInterface $payload
     *
     * @return string
     */
    abstract protected function body(PayloadInterface $payload);
}
