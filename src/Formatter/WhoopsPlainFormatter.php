<?php

namespace Equip\Formatter;

use Equip\Formatter\FormatterInterface;
use Equip\Formatter\WhoopsRenderTrait;
use Whoops\Handler\PlainTextHandler as PlainHandler;
use Whoops\Run as Whoops;

class WhoopsPlainFormatter implements FormatterInterface
{
    use WhoopsRenderTrait;

    /**
     * @param Whoops $whoops
     * @param PlainHandler $handler
     */
    public function __construct(
        Whoops $whoops,
        PlainHandler $handler
    ) {
        $this->whoops = $whoops;
        $this->whoops->pushHandler($handler);
    }

    /**
     * @inheritDoc
     */
    public static function accepts()
    {
        return ['text/plain'];
    }

    /**
     * @inheritDoc
     */
    public function type()
    {
        return 'text/plain';
    }
}
