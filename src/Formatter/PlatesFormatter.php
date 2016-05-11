<?php

namespace Equip\Formatter;

use Equip\Adr\PayloadInterface;
use League\Plates\Engine;

class PlatesFormatter extends HtmlFormatter
{
    /**
     * @var Engine
     */
    protected $engine;

    /**
     * @param Engine $engine
     */
    public function __construct(Engine $engine)
    {
        $this->engine = $engine;
    }

    /**
     * @inheritDoc
     */
    public function body(PayloadInterface $payload)
    {
        return $this->render($payload);
    }

    /**
     * @param PayloadInterface $payload
     *
     * @return string The rendered template
     */
    protected function render(PayloadInterface $payload)
    {
        $template = $payload->getSetting('template');
        $output = $payload->getOutput();

        return $this->engine->render($template, $output);
    }
}
