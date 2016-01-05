<?php

namespace Equip;

use Arbiter\Action as Arbiter;

class Action extends Arbiter
{
    protected $input = 'Equip\Input';
    protected $responder = 'Equip\Responder\ChainedResponder';

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
