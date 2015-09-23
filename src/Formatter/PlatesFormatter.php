<?php

namespace Spark\Formatter;

use League\Plates\Engine;
use League\Plates\Template\Template;
use Spark\Adr\PayloadInterface;

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
     * @param  PayloadInterface $payload
     * @return string
     */
    protected function template(PayloadInterface $payload)
    {
        return $payload->getOutput()['template'];
    }

    /**
     * @param  Template $template
     * @param  PayloadInterface $payload
     * @return string
     */
    protected function render(Template $template, PayloadInterface $payload)
    {
        return $template->render($payload->getOutput());
    }

    public function body(PayloadInterface $payload)
    {
        $template = $this->template($payload);
        $template = $this->engine->make($template);
        return $this->render($template, $payload);
    }
}
