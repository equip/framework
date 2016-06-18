<?php

namespace Equip\Formatter;

use Equip\Formatter\WhoopsRenderTrait;
use Whoops\Handler\JsonResponseHandler as JsonHandler;
use Whoops\Run as Whoops;

class WhoopsJsonFormatter extends JsonFormatter
{
    use WhoopsRenderTrait;

    /**
     * @param Whoops $whoops
     * @param JsonHandler $handler
     */
    public function __construct(
        Whoops $whoops,
        JsonHandler $handler
    ) {
        $this->whoops = $whoops;
        $this->whoops->pushHandler($handler);
    }
}
