<?php

namespace Spark;

use Arbiter\Action as Arbiter;

class Action extends Arbiter
{
    protected $input = 'Spark\Input';
    protected $responder = 'Spark\Responder\ChainedResponder';

    /**
     * @inheritDoc
     */
    public function __construct(
        $domain,
        $responder = null,
        $input = null
    ) {
        $this->domain = $domain;

        if ($responder) {
            $this->responder = $responder;
        }

        if ($input) {
            $this->input = $input;
        }
    }
}
