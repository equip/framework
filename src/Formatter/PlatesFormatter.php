<?php

namespace Equip\Formatter;

use Equip\Adr\PayloadInterface;
use League\Plates\Engine;
use League\Plates\Template\Template;
use Lukasoppermann\Httpstatus\Httpstatus;

class PlatesFormatter extends HtmlFormatter
{
    /**
     * @var Engine
     */
    protected $engine;

    /**
     * @param Engine $engine
     * @param Httpstatus $http_status
     */
    public function __construct(
        Engine $engine,
        Httpstatus $http_status
    ) {
        $this->engine = $engine;
        parent::__construct($http_status);
    }

    /**
     * @inheritDoc
     */
    public function body(PayloadInterface $payload)
    {
        $template = $this->template($payload);
        $template = $this->engine->make($template);
        return $this->render($template, $payload);
    }

    /**
     * @param PayloadInterface $payload
     * @return string
     */
    protected function template(PayloadInterface $payload)
    {
        return $payload->getOutput()['template'];
    }

    /**
     * @param Template $template
     * @param PayloadInterface $payload
     * @return string
     */
    protected function render(Template $template, PayloadInterface $payload)
    {
        return $template->render($payload->getOutput());
    }
}
