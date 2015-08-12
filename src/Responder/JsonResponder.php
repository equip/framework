<?php

namespace Spark\Responder;

use Spark\Adr\PayloadInterface;

class JsonResponder extends AbstractResponder
{
    public function accepts()
    {
        return ['application/json'];
    }

    protected function type()
    {
        return 'application/json';
    }

    protected function options()
    {
        return 0;
    }

    protected function body(PayloadInterface $payload)
    {
        return json_encode($payload->getOutput(), $this->options());
    }
}
