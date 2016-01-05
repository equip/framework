<?php

namespace Equip\Formatter;

use Equip\Adr\PayloadInterface;

class JsonFormatter extends AbstractFormatter
{
    /**
     * @inheritDoc
     */
    public static function accepts()
    {
        return ['application/json'];
    }

    /**
     * @inheritDoc
     */
    public function type()
    {
        return 'application/json';
    }

    /**
     * @inheritDoc
     */
    public function body(PayloadInterface $payload)
    {
        return json_encode($payload->getOutput(), $this->options());
    }

    /**
     * @inheritDoc
     */
    protected function options()
    {
        return 0;
    }
}
