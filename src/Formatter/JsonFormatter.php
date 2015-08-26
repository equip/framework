<?php

namespace Spark\Formatter;

use Spark\Adr\PayloadInterface;

class JsonFormatter extends AbstractFormatter
{
    public static function accepts()
    {
        return ['application/json'];
    }

    public function type()
    {
        return 'application/json';
    }

    protected function options()
    {
        return 0;
    }

    public function body(PayloadInterface $payload)
    {
        return json_encode($payload->getOutput(), $this->options());
    }
}
