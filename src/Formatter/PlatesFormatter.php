<?php

namespace Equip\Formatter;

use Equip\Formatter\HtmlFormatter;
use League\Plates\Engine;

class PlatesFormatter extends HtmlFormatter
{
    /**
     * @var Engine
     */
    private $engine;

    /**
     * @var string
     */
    private $template;

    /**
     * @param Engine $engine
     */
    public function __construct(Engine $engine)
    {
        $this->engine = $engine;
    }

    /**
     * Get a copy that uses a different template.
     *
     * @param string $template
     *
     * @return static
     */
    public function withTemplate($template)
    {
        $copy = clone $this;
        $copy->template = $template;

        return $copy;
    }

    /**
     * @inheritDoc
     */
    public function format($content)
    {
        return $this->engine->render($this->template, $content);
    }
}
