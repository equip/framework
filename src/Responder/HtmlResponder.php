<?php

namespace Spark\Responder;

use Spark\Adr\PayloadInterface;

class HtmlResponder extends AbstractResponder
{
    public function accepts()
    {
        return ['text/html'];
    }

    protected function type()
    {
        return 'text/html';
    }

    protected function body(PayloadInterface $payload)
    {
        return implode("\n", $payload->getOutput());
    }
}
