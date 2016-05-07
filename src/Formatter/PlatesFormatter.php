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
     * @return string
     */
    protected function render(PayloadInterface $payload)
    {
        $template = $this->template($payload);
        $output = $this->output($payload);

        return $this->engine->render($template, $output);
    }

    /**
     * @param PayloadInterface $payload
     *
     * @return string Template name
     */
    protected function template(PayloadInterface $payload)
    {
        return $payload->getOutput()['template'];
    }

    /**
     * @param PayloadInterface $payload
     *
     * @return array $output
     */
    protected function output(PayloadInterface $payload)
    {
        $output = $payload->getOutput();
        unset($output['template']);

        return $output;
    }
}
