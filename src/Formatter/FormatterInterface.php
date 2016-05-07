<?php

namespace Equip\Formatter;

use Equip\Adr\PayloadInterface;

interface FormatterInterface
{
    /**
     * Get the content types this formatter can satisfy.
     *
     * @return array
     */
    public static function accepts();

    /**
     * Get the content type of the response body.
     *
     * @return string
     */
    public function type();

    /**
     * Get the response body from the payload.
     *
     * @param PayloadInterface $payload
     *
     * @return string
     */
    public function body(PayloadInterface $payload);
}
