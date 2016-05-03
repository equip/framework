<?php

namespace Equip;

use Equip\Input;
use Equip\Responder\ChainedResponder;

class Action
{
    /**
     * The domain specification.
     *
     * @var DomainInterface
     */
    protected $domain;

    /**
     * The responder specification.
     *
     * @var ResponderInterface
     */
    protected $responder = ChainedResponder::class;

    /**
     * The input specification.
     *
     * @var InputInterface
     */
    protected $input = Input::class;

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

    /**
     * Returns the domain specification.
     *
     * @return DomainInterface
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Returns the responder specification.
     *
     * @return ResponderInterface
     */
    public function getResponder()
    {
        return $this->responder;
    }

    /**
     * Returns the input specification.
     *
     * @return InputInterface
     */
    public function getInput()
    {
        return $this->input;
    }
}
