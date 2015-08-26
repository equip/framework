<?php

namespace Spark\Formatter;

use Spark\Adr\PayloadInterface;

class HtmlFormatter extends AbstractFormatter
{
    public static function accepts()
    {
        return ['text/html'];
    }

    public function type()
    {
        return 'text/html';
    }

    public function body(PayloadInterface $payload)
    {
        return implode("\n", $payload->getOutput());
    }
}
