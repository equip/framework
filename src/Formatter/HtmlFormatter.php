<?php

namespace Equip\Formatter;

use Equip\Adr\PayloadInterface;

abstract class HtmlFormatter implements FormatterInterface
{
    /**
     * @inheritDoc
     */
    public static function accepts()
    {
        return ['text/html'];
    }

    /**
     * @inheritDoc
     */
    public function type()
    {
        return 'text/html';
    }
}
