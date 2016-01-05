<?php

namespace Equip\Formatter;

use Equip\Adr\PayloadInterface;

abstract class HtmlFormatter extends AbstractFormatter
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
