<?php

namespace Equip\Formatter;

use Equip\Adr\PayloadInterface;
use Equip\Formatter\FormatterInterface;

class JsonFormatter implements FormatterInterface
{
    /**
     * @inheritDoc
     */
    public static function accepts()
    {
        return [
            'application/json',
            'application/javascript',
            'application/ld+json',
            'application/vnd.api+json',
            'application/vnd.geo+json',
        ];
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
