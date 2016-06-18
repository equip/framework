<?php

namespace Equip\Formatter;

use Equip\Formatter\WhoopsRenderTrait;
use Whoops\Handler\PrettyPageHandler as HtmlHandler;
use Whoops\Run as Whoops;

class WhoopsHtmlFormatter extends HtmlFormatter
{
    use WhoopsRenderTrait;

    /**
     * @param Whoops $whoops
     * @param HtmlHandler $handler
     */
    public function __construct(
        Whoops $whoops,
        HtmlHandler $handler
    ) {
        $this->whoops = $whoops;
        $this->whoops->pushHandler($handler);
    }
}
