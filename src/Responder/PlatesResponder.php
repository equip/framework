<?php

namespace Spark\Responder;

use League\Plates\Engine;
use Spark\Responder\AbstractResponder;

class PlatesResponder extends AbstractResponder
{
    /**
     * @var Engine
     */
    protected $engine;

    public function __construct(Engine $engine)
    {
        $this->engine = $engine;
    }

    public static function accepts()
    {
        return ['text/html'];
    }

    protected function getTemplate($data)
    {
        $extras = $this->payload->getExtras();
        return $extras['template'];
    }

    protected function responseBody($data)
    {
        $template = $this->getTemplate($data);
        $template = $this->engine->make($template);
        $rendered = $template->render($data);

        $this->response = $this->response->withHeader('Content-Type', 'text/html');
        $this->response->getBody()->write($rendered);
    }
}
