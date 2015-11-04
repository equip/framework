<?php

namespace Spark\Formatter;

use Spark\Adr\PayloadInterface;

abstract class HtmlFormatter extends AbstractFormatter
{
    public static function accepts()
    {
        return ['text/html'];
    }

    public function type()
    {
        return 'text/html';
    }
}
