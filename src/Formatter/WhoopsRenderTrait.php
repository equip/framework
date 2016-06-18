<?php

namespace Equip\Formatter;

use Equip\Adr\PayloadInterface;

trait WhoopsRenderTrait
{
    /**
     * @var Whoops
     */
    private $whoops;

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
    private function render(PayloadInterface $payload)
    {
        $exception = $this->exception($payload);

        return $this->whoops->handleException($exception);
    }

    /**
     * @param PayloadInterface $payload
     *
     * @return Throwable|Exception
     */
    private function exception(PayloadInterface $payload)
    {
        return $payload->getOutput()['exception'];
    }
}
