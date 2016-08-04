<?php

namespace Equip\Formatter;

class JsonFormatter implements FormatterInterface
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
    public function format($content)
    {
        return json_encode($content, $this->options());
    }

    /**
     * @inheritDoc
     */
    protected function options()
    {
        return 0;
    }
}
