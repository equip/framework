<?php

namespace Equip\Formatter;

interface FormatterInterface
{
    /**
     * Get the content types this formatter can satisfy.
     *
     * @return array
     */
    public static function accepts();

    /**
     * Get the content type this formatter will generate.
     *
     * @return string
     */
    public function type();

    /**
     * Get the formatted version of provided content.
     *
     * @param mixed $content
     *
     * @return string
     */
    public function format($content);
}
